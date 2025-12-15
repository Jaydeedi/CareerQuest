import AuditLogTable from '../AuditLogTable';
import type { AuditLog } from "@shared/schema";

const mockLogs: AuditLog[] = [
  {
    id: "1",
    createdAt: new Date("2025-01-15T14:32:01"),
    userId: "admin@careerquest.com",
    action: "Syllabus Upload",
    details: "Uploaded CS301_DataStructures.pdf - Generated 45 questions for Full Stack Development path",
    status: "success",
    ipAddress: null
  },
  {
    id: "2",
    createdAt: new Date("2025-01-15T13:15:22"),
    userId: "emily.davis@student.edu",
    action: "Quiz Completion",
    details: "Completed 'Algorithms Mastery Quiz' - Score: 85% - XP Earned: 250",
    status: "success",
    ipAddress: null
  },
  {
    id: "3",
    createdAt: new Date("2025-01-15T12:08:45"),
    userId: "john.smith@student.edu",
    action: "Assessment Terminated",
    details: "Tab switch detected during 'Final Assessment CS401' - Auto-submitted",
    status: "warning",
    ipAddress: null
  },
  {
    id: "4",
    createdAt: new Date("2025-01-15T11:45:11"),
    userId: "admin@careerquest.com",
    action: "User Account Created",
    details: "Created new student account for sarah.connor@student.edu - Assigned to AI-guided path",
    status: "success",
    ipAddress: null
  },
  {
    id: "5",
    createdAt: new Date("2025-01-15T10:22:33"),
    userId: "system",
    action: "Career Path Assignment Failed",
    details: "Unable to assign career path to user_12345 - Insufficient data collected",
    status: "error",
    ipAddress: null
  }
];

export default function AuditLogTableExample() {
  return <AuditLogTable logs={mockLogs} />;
}
