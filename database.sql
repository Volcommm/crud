USE `crud_with_login`;

CREATE TABLE `login` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `qty` int(5) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `login_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT FK_products_1
  FOREIGN KEY (login_id) REFERENCES login(id)
  ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- Inserimento di dati nella tabella `login`
INSERT INTO `login` (`name`, `email`, `username`, `password`) 
VALUES 
('Simone Rossi', 'simone.rossi@example.com', 'simone', MD5('password123')),
('Maria Bianchi', 'maria.bianchi@example.com', 'maria', MD5('mariapass')),
('Luca Verdi', 'luca.verdi@example.com', 'luca', MD5('lucapwd')),
('Giulia Neri', 'giulia.neri@example.com', 'giulia', MD5('giulia2024'));

-- Inserimento di dati nella tabella `products`
INSERT INTO `products` (`name`, `qty`, `price`, `login_id`) 
VALUES 
('Laptop', 5, 899.99, 1),
('Smartphone', 10, 499.50, 2),
('Tablet', 7, 299.99, 1),
('Headphones', 15, 49.99, 3),
('Monitor', 8, 199.99, 4);

