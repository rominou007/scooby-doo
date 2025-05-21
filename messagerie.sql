-- Création de la table des conversations
CREATE TABLE `conversations` (
  `conversation_id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user1_id` BIGINT(20) UNSIGNED NOT NULL,
  `user2_id` BIGINT(20) UNSIGNED NOT NULL,
  `date_creation` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_message_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`conversation_id`),
  KEY `user1_id` (`user1_id`),
  KEY `user2_id` (`user2_id`),
  CONSTRAINT `conversations_user1_fk` FOREIGN KEY (`user1_id`) REFERENCES `user` (`id_user`) ON DELETE CASCADE,
  CONSTRAINT `conversations_user2_fk` FOREIGN KEY (`user2_id`) REFERENCES `user` (`id_user`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Création de la table des messages
CREATE TABLE `messages` (
  `message_id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `conversation_id` BIGINT(20) UNSIGNED NOT NULL,
  `sender_id` BIGINT(20) UNSIGNED NOT NULL,
  `content` TEXT NOT NULL,
  `date_envoi` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lu` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`message_id`),
  KEY `conversation_id` (`conversation_id`),
  KEY `sender_id` (`sender_id`),
  CONSTRAINT `messages_conversation_fk` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`conversation_id`) ON DELETE CASCADE,
  CONSTRAINT `messages_sender_fk` FOREIGN KEY (`sender_id`) REFERENCES `user` (`id_user`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;