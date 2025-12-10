import express, { type Request, Response, NextFunction } from "express";
import session from "express-session";
import memorystore from "memorystore";
import passport from "passport";
import helmet from "helmet"; // Import helmet
import rateLimit from "express-rate-limit"; // Import express-rate-limit
import { registerRoutes } from "./routes";
import { setupVite, serveStatic, log } from "./vite";
import "./auth"; // Initialize passport strategies
import "./firebase"; // Initialize Firebase
import { storage } from "./storage-firestore";

const app = express();

// Trust proxy - required for rate limiting behind reverse proxy (Replit)
app.set('trust proxy', 1);

declare module 'http' {
  interface IncomingMessage {
    rawBody: unknown
  }
}

// Session configuration (using in-memory store for Firebase setup)
// Use Memorystore as a more robust in-memory store (reduces in-process eviction)
const MemoryStore = memorystore(session);

app.use(session({
  store: new MemoryStore({
    checkPeriod: 86400000 // prune expired entries every 24 hours
  }),
  secret: process.env.SESSION_SECRET || "careerquest-firebase-session",
  resave: false,
  saveUninitialized: false,
  // Refresh cookie expiry on each response to keep active sessions alive
  rolling: true,
  cookie: {
    secure: process.env.NODE_ENV === "production",
    httpOnly: true,
    sameSite: 'lax',
    maxAge: 1000 * 60 * 60 * 24 * 7 // 7 days
  }
}));


// Passport middleware
app.use(passport.initialize());
app.use(passport.session());

// Security middleware
app.use(helmet({
  contentSecurityPolicy: false, // Disable for Vite dev
}));

// Rate limiting
const limiter = rateLimit({
  windowMs: 15 * 60 * 1000, // 15 minutes
  max: parseInt(process.env.RATE_LIMIT || '100', 10), // Limit each IP (configurable)
  message: "Too many requests from this IP, please try again later.",
  standardHeaders: true,
  legacyHeaders: false,
});

// Helper to skip rate limiting for certain safe endpoints or authenticated users
function shouldSkipRateLimit(req: any) {
  try {
    const safePaths = new Set([
      '/api/auth/me',
      '/api/users/me/badges',
      '/api/notifications',
      '/api/badges',
      '/api/users/me/progress',
      '/api/study-suggestions'
    ]);

    if (safePaths.has(req.path)) return true;
    if (typeof req.isAuthenticated === 'function' && req.isAuthenticated()) return true;
  } catch (err) {
    // ignore
  }
  return false;
}

const authLimiter = rateLimit({
  windowMs: 15 * 60 * 1000,
  max: parseInt(process.env.AUTH_RATE_LIMIT || '100', 10), // configurable
  message: "Too many authentication attempts, please try again later.",
  standardHeaders: true,
  legacyHeaders: false,
  skip: shouldSkipRateLimit,
});

if (app.get('env') === 'development') {
  console.log('[RATE LIMIT] Development environment detected — skipping global API limiter');
} else {
  // Apply global limiter but allow skipping for safe endpoints
  app.use("/api/", rateLimit({
    ...limiter,
    skip: shouldSkipRateLimit,
  }));
  app.use("/api/auth/login", authLimiter);
  app.use("/api/auth/register", authLimiter);
}

app.use(express.json({
  verify: (req, _res, buf) => {
    req.rawBody = buf;
  }
}));
app.use(express.urlencoded({ extended: false }));

app.use((req, res, next) => {
  const start = Date.now();
  const path = req.path;
  let capturedJsonResponse: Record<string, any> | undefined = undefined;

  const originalResJson = res.json;
  res.json = function (bodyJson, ...args) {
    capturedJsonResponse = bodyJson;
    return originalResJson.apply(res, [bodyJson, ...args]);
  };

  res.on("finish", () => {
    const duration = Date.now() - start;
    if (path.startsWith("/api")) {
      let logLine = `${req.method} ${path} ${res.statusCode} in ${duration}ms`;
      if (capturedJsonResponse) {
        logLine += ` :: ${JSON.stringify(capturedJsonResponse)}`;
      }

      if (logLine.length > 80) {
        logLine = logLine.slice(0, 79) + "…";
      }

      log(logLine);
    }
  });

  next();
});

(async () => {
  const server = await registerRoutes(app);
  
  // Initialize badge definitions in Firestore
  await storage.seedBadges();

  app.use((err: any, _req: Request, res: Response, _next: NextFunction) => {
    const status = err.status || err.statusCode || 500;
    const message = err.message || "Internal Server Error";

    res.status(status).json({ message });
    throw err;
  });

  // importantly only setup vite in development and after
  // setting up all the other routes so the catch-all route
  // doesn't interfere with the other routes
  if (app.get("env") === "development") {
    await setupVite(app, server);
  } else {
    serveStatic(app);
  }

  // ALWAYS serve the app on the port specified in the environment variable PORT
  // Other ports are firewalled. Default to 5000 if not specified.
  // this serves both the API and the client.
  // It is the only port that is not firewalled.
  const port = parseInt(process.env.PORT || '5000', 10);
  server.listen({
    port,
    host: "0.0.0.0",
    reusePort: true,
  }, () => {
    log(`serving on port ${port}`);
  });
})();