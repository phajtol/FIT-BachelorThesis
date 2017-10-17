ALTER TABLE `author`
ADD `user_id` int unsigned NULL;

ALTER TABLE `author`
ADD FOREIGN KEY (`user_id`) REFERENCES `submitter` (`id`);