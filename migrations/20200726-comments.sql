CREATE TABLE habit_comment (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    habit_id INT UNSIGNED NOT NULL,
    type enum('month', 'week') NOT NULL,
    date DATE NOT NULL,
    comment TEXT NOT NULL,
    UNIQUE INDEX (habit_id, type, date)
);

ALTER TABLE habit_comment ADD CONSTRAINT fk_habit_comment_habit_id FOREIGN KEY (habit_id) REFERENCES habit(id);
