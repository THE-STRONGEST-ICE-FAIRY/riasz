ALTER TABLE courseoutcomes
    ADD COLUMN IF NOT EXISTS program_id INT DEFAULT NULL AFTER io_id,
    ADD INDEX IF NOT EXISTS idx_courseoutcomes_program_id (program_id);

ALTER TABLE assessmentcontents
    ADD COLUMN IF NOT EXISTS ass_content_help_text TEXT DEFAULT NULL AFTER ass_content_question,
    ADD COLUMN IF NOT EXISTS ass_content_response_type VARCHAR(20) NOT NULL DEFAULT 'yes_no' AFTER ass_content_help_text,
    ADD COLUMN IF NOT EXISTS ass_content_weight DECIMAL(5,2) DEFAULT NULL AFTER ass_content_response_type,
    ADD COLUMN IF NOT EXISTS ass_content_order INT NOT NULL DEFAULT 0 AFTER ass_content_weight;
