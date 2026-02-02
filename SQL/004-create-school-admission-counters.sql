-- Migration: create school_admission_counters
CREATE TABLE IF NOT EXISTS school_admission_counters (
  id INT AUTO_INCREMENT PRIMARY KEY,
  school_id INT NOT NULL,
  session_id INT NOT NULL,
  last_number INT NOT NULL DEFAULT 0,
  UNIQUE KEY uq_school_session (school_id, session_id)
);

-- Optional: add indexes for faster lookups
ALTER TABLE school_admission_counters
  ADD INDEX idx_school_id (school_id),
  ADD INDEX idx_session_id (session_id);
