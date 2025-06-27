-- Add rating column to existing feedback table
-- Run this script if you already have a feedback table without the rating column

ALTER TABLE feedback 
ADD COLUMN rating INT NOT NULL DEFAULT 5 CHECK (rating >= 1 AND rating <= 5) 
AFTER feedback;

-- Update existing records to have a default rating of 5
UPDATE feedback SET rating = 5 WHERE rating IS NULL; 