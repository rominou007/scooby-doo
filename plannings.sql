-- modif de la bdd pour la partie planning

CREATE TABLE plannings (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT(20) UNSIGNED NOT NULL,
    module_id BIGINT(20) UNSIGNED NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `user_id` (`user_id`),
    KEY `module_id` (`module_id`),
    CONSTRAINT `planning_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
    CONSTRAINT `planning_module_fk` FOREIGN KEY (`module_id`) REFERENCES `modules` (`module_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
