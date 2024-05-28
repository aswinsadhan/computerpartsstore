-- migrate:up
CREATE TABLE IF NOT EXISTS tbl_category (
    category_id int PRIMARY KEY AUTO_INCREMENT,
    category_name varchar(30) NOT NULL,
    category_description TEXT NOT NULL,
    date_added DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status boolean NOT NULL DEFAULT 1
);

-- migrate:down
DROP TABLE tbl_category;
