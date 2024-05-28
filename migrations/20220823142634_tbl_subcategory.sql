-- migrate:up
CREATE TABLE IF NOT EXISTS tbl_subcategory (
    subcategory_id int PRIMARY KEY AUTO_INCREMENT,
    category_id int NOT NULL,
    subcategory_name varchar(30) NOT NULL,
    subcategory_description TEXT NOT NULL,
    date_added DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status boolean NOT NULL DEFAULT 1,
    FOREIGN KEY (category_id) REFERENCES tbl_category(category_id)
);


-- migrate:down
DROP TABLE tbl_subcategory;

