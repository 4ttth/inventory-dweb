-- Create the database
CREATE DATABASE InventoryDB;
USE InventoryDB;

-- Shelf Table
CREATE TABLE Shelf (
    ShelfID INT PRIMARY KEY, 
    ShelfLocation VARCHAR(50) NOT NULL, 
    ShelfSize VARCHAR(50) NOT NULL
);

-- User Table
CREATE TABLE User (
    UserID INT AUTO_INCREMENT PRIMARY KEY, 
    FirstName VARCHAR(50) NOT NULL, 
    LastName VARCHAR(50) NOT NULL, 
    Username VARCHAR(50) UNIQUE NOT NULL, 
    Password VARCHAR(50) NOT NULL, 
    Role ENUM('Admin', 'Officer', 'Borrower') NOT NULL
);

-- Category Table
CREATE TABLE Category (
    CategoryID INT AUTO_INCREMENT PRIMARY KEY, 
    CategoryName VARCHAR(50) NOT NULL
);

-- Item Table
CREATE TABLE Item (
    ItemID INT AUTO_INCREMENT PRIMARY KEY, 
    ItemName VARCHAR(50) NOT NULL, 
    Quantity INT NOT NULL CHECK (Quantity >= 0), 
    ShelfID INT, 
    CategoryID INT, 
    FOREIGN KEY (ShelfID) REFERENCES Shelf(ShelfID) ON DELETE SET NULL, 
    FOREIGN KEY (CategoryID) REFERENCES Category(CategoryID) ON DELETE SET NULL
);

-- transaction Table
CREATE TABLE transaction (
    TransactionID INT AUTO_INCREMENT PRIMARY KEY, 
    ItemID INT, 
    TransactionType ENUM('Borrow', 'Return', 'Disposal') NOT NULL, 
    Quantity INT NOT NULL CHECK (Quantity > 0), 
    Date DATE NOT NULL, 
    Time TIME NOT NULL, 
    UserID INT, 
    FOREIGN KEY (ItemID) REFERENCES Item(ItemID) ON DELETE CASCADE, 
    FOREIGN KEY (UserID) REFERENCES User(UserID) ON DELETE CASCADE
);
