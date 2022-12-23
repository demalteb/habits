ALTER TABLE habit ADD is_fulfilment_relative TINYINT UNSIGNED NOT NULL DEFAULT 1;
ALTER TABLE habit ADD fulfilment_unit VARCHAR(10) NOT NULL DEFAULT '%';
ALTER TABLE habit ADD fulfilment_max INT UNSIGNED NULL DEFAULT 100;

CREATE TABLE habit_weekday (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    habit_id INT NOT NULL,
    day_number INT NOT NULL DEFAULT 0, /* starting on MONDAY with 0 */
    created_time datetime not null,
    changed_time datetime not null,
    UNIQUE INDEX(habit_id, day_number),
    CONSTRAINT fk_habit_weekday_habit_id FOREIGN KEY (habit_id) REFERENCES habit(id) ON DELETE CASCADE ON UPDATE CASCADE
) DEFAULT CHARSET=utf8;

CREATE TABLE habit_pause (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    habit_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    created_time datetime not null,
    changed_time datetime not null,
    UNIQUE INDEX(habit_id, start_date),
    CONSTRAINT fk_habit_pause_habit_id FOREIGN KEY (habit_id) REFERENCES habit(id) ON DELETE CASCADE ON UPDATE CASCADE
) DEFAULT CHARSET=utf8;
