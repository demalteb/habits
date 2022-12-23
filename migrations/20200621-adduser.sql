CREATE TABLE user (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    login VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_time datetime not null,
    changed_time datetime not null,
    UNIQUE INDEX(name),
    UNIQUE INDEX(login)
) DEFAULT CHARSET=utf8;

INSERT INTO user VALUES(1, 'Demalteb', 'demalteb', '', NOW(), NOW());

ALTER TABLE habit ADD user_id INT NOT NULL;
UPDATE habit SET user_id=1;
ALTER TABLE habit ADD CONSTRAINT fk_habit_user_id FOREIGN KEY (user_id) REFERENCES user(id);
