CREATE TABLE pars (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title TEXT,
    summary TEXT,
    hash CHAR(32) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);