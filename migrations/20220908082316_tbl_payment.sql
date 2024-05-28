-- migrate:up
CREATE TABLE IF NOT EXISTS tbl_payment (
    payment_id int PRIMARY KEY AUTO_INCREMENT,
    card_id int NOT NULL,
    order_id int NOT NULL,
    courier_id int NOT NULL,
    date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (card_id) REFERENCES tbl_card(card_id),
    FOREIGN KEY (order_id) REFERENCES tbl_order(order_id),
    FOREIGN KEY (courier_id) REFERENCES tbl_courier(courier_id)
);


-- migrate:down
DROP TABLE tbl_payment;
