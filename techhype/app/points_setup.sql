-- Points / Rewards System
-- Run this SQL in your `techhype` database

CREATE TABLE IF NOT EXISTS points_log (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    points      INT NOT NULL,            -- positive = earned, negative = redeemed
    type        ENUM('earn','redeem','refund') NOT NULL,
    description VARCHAR(255) NOT NULL,
    order_id    INT DEFAULT NULL,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
