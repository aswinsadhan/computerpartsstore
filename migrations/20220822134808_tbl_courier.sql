-- migrate:up
CREATE TABLE IF NOT EXISTS tbl_courier (
    courier_id int PRIMARY KEY AUTO_INCREMENT,
    staff_id int NOT NULL,
    email varchar(50) NOT NULL,
    courier_name varchar(30) NOT NULL,
    courier_building_name varchar(20) NOT NULL,
    courier_street varchar(20) NOT NULL,
    courier_city varchar(20) NOT NULL,
    courier_state varchar(20) NOT NULL,
    courier_pincode varchar(7) NOT NULL,
    courier_phone varchar(10) NOT NULL,
    date_added DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (email) REFERENCES tbl_login(email),
    FOREIGN KEY (staff_id) REFERENCES tbl_staff(staff_id)
);

-- migrate:down
DROP TABLE tbl_courier;
