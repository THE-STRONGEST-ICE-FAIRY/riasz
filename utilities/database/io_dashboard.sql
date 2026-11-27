CREATE TABLE IF NOT EXISTS io_dashboard_settings (
    user_id INT NOT NULL,
    academic_year_start DATE NOT NULL,
    academic_year_end DATE NOT NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS io_dashboard_event_details (
    event_id INT NOT NULL,
    owner_user_id INT NOT NULL,
    event_type VARCHAR(20) NOT NULL DEFAULT 'event',
    start_time TIME DEFAULT NULL,
    end_time TIME DEFAULT NULL,
    target_role VARCHAR(50) DEFAULT NULL,
    target_school_id INT DEFAULT NULL,
    target_program_id INT DEFAULT NULL,
    notify_all TINYINT(1) NOT NULL DEFAULT 0,
    notification_method VARCHAR(20) NOT NULL DEFAULT 'system',
    PRIMARY KEY (event_id),
    KEY idx_dashboard_event_owner (owner_user_id, event_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
