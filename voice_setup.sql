-- شغّل الكود ده مرة واحدة في تبويب SQL في phpMyAdmin
-- (بعد ما شغّلت setup.sql الأول اللي فيه جدول robot_state)

CREATE TABLE voice_texts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    text_content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
