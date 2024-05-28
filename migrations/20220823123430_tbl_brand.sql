-- migrate:up
CREATE TABLE IF NOT EXISTS tbl_brand (
    brand_id int PRIMARY KEY AUTO_INCREMENT,
    brand_name varchar(30) NOT NULL,
    brand_description TEXT NOT NULL,
    date_added DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status boolean NOT NULL DEFAULT 1
);


-- migrate:down
DROP TABLE tbl_brand;
