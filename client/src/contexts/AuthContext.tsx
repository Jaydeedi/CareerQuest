import { createContext, useContext, useState, useEffect, useCallback } from "react";
import { useLocation } from "wouter";
import { apiRequest } from "@/lib/queryClient";
import type { User } from "@shared/schema";

interface AuthContextType {
  user: Omit<User, "password"> | null;
  isLoading: boolean;
  login: (email: string, password: string) => Promise<void>;
  register: (data: {
    email: string;
    username: string;
    password: string;
    confirmPassword: string;
    displayName: string;
    pathSelectionMode?: string;
    currentCareerPathId?: string | null;
  }) => Promise<void>;
  logout: () => Promise<void>;
  refreshUser: () => Promise<void>;
  pendingRecommendation: any | null;
  setPendingRecommendation: (recommendation: any) => void;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [user, setUser] = useState<Omit<User, "password"> | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [pendingRecommendation, setPendingRecommendation] = useState<any>(null);
  const [, setLocation] = useLocation();

  // Check if user is logged in on mount
  useEffect(() => {
    checkAuth();
  }, []);

  async function checkAuth() {
    try {
      const response = await fetch("/api/auth/me", {
        credentials: "include",
      });
      if (response.ok) {
        const data = await response.json();
        setUser(data.user);
      } else if (response.status === 429) {
        // Rate limited — transient. Don't log the user out immediately.
        // Retry after a short backoff so temporary rate limits don't cause logout.
        console.warn('Auth check rate-limited (429). Retrying in 5s.');
        setTimeout(checkAuth, 5000);
        return;
      } else {
        setUser(null);
      }
    } catch (error) {
      console.error("Auth check failed:", error);
      setUser(null);
    } finally {
      setIsLoading(false);
    }
  }

  async function login(email: string, password: string) {
    const response = await apiRequest("POST", "/api/auth/login", { email, password });
    const data = await response.json();

    setUser(data.user);
    
    // Redirect admin users to admin dashboard
    if (data.user.isAdmin) {
      setLocation("/admin");
    } else {
      setLocation("/dashboard");
    }
  }

  async function register(data: {
    email: string;
    username: string;
    password: string;
    confirmPassword: string;
    displayName: string;
    pathSelectionMode?: string;
    currentCareerPathId?: string | null;
  }) {
    const response = await apiRequest("POST", "/api/auth/register", data);
    const json = await response.json();

    setUser(json.user);
    
    // Redirect admin users to admin dashboard
    if (json.user.isAdmin) {
      setLocation("/admin");
    } else {
      setLocation("/dashboard");
    }
  }

  async function logout() {
    await apiRequest("POST", "/api/auth/logout");

    setUser(null);
    setLocation("/");
  }

  const refreshUser = useCallback(async () => {
    await checkAuth();
  }, []);

  return (
    <AuthContext.Provider value={{ user, isLoading, login, register, logout, refreshUser, pendingRecommendation, setPendingRecommendation }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  const context = useContext(AuthContext);
  if (context === undefined) {
    throw new Error("useAuth must be used within an AuthProvider");
  }
  return context;
}
