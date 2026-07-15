CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE polls (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    expires_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_user FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE options (
    id INT AUTO_INCREMENT PRIMARY KEY,
    poll_id INT NOT NULL,
    option_text VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_poll FOREIGN KEY (poll_id) REFERENCES polls(id)
);

CREATE TABLE votes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    poll_id INT NOT NULL,
    option_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_poll_vote FOREIGN KEY (poll_id) REFERENCES polls(id),
    CONSTRAINT fk_option_vote FOREIGN KEY (option_id) REFERENCES options(id),
    CONSTRAINT fk_user_vote FOREIGN KEY (user_id) REFERENCES users(id),
    CONSTRAINT unique_user_poll UNIQUE (user_id, poll_id)
);

SELECT id, name, email, password
FROM users;

SELECT * FROM polls;
SELECT * FROM options;
SELECT * FROM votes;

ALTER TABLE options
DROP FOREIGN KEY fk_poll;

ALTER TABLE options
ADD CONSTRAINT fk_poll
FOREIGN KEY (poll_id)
REFERENCES polls(id)
ON DELETE CASCADE;

ALTER TABLE votes
DROP FOREIGN KEY fk_poll_vote;

ALTER TABLE votes
DROP FOREIGN KEY fk_option_vote;

ALTER TABLE votes
ADD CONSTRAINT fk_poll_vote
FOREIGN KEY (poll_id)
REFERENCES polls(id)
ON DELETE CASCADE;

ALTER TABLE votes
ADD CONSTRAINT fk_option_vote
FOREIGN KEY (option_id)
REFERENCES options(id)
ON DELETE CASCADE;

SELECT * FROM polls WHERE id = 2;
SELECT * FROM options WHERE poll_id = 2;
SELECT * FROM votes WHERE poll_id = 2;