-- شغّل الكود ده مرة واحدة في تبويب SQL في phpMyAdmin

CREATE TABLE robot_state (
    id INT PRIMARY KEY,
    command CHAR(1) NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- إدخال الصف الوحيد اللي هيتحدث باستمرار (id = 1)
INSERT INTO robot_state (id, command) VALUES (1, 'S');
