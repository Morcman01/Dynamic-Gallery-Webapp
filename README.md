# Photo Gallery Web Application

A dynamic photo gallery web application built with the LAMP stack, featuring a vanilla HTML/CSS/JavaScript frontend, PHP backend, and MariaDB database.

## Overview

This project is a full-stack photo gallery application developed as a practical project for learning and applying web development fundamentals.

The application allows users to browse photos, create accounts, authenticate using username and password, and access functionality based on their assigned role.

The project was developed using a traditional LAMP architecture and does not rely on frontend frameworks or backend frameworks.

## Features

- Dynamic photo gallery
- User registration and login
- Session-based authentication
- Role-based authorization
- Viewer and uploader roles
- Image uploading
- Image deletion
- Password hashing
- Prepared SQL statements
- Server-side file validation
- File size restrictions
- Responsive gallery interface
- Fullscreen photo viewer
- Previous/next photo navigation
- JSON-based authentication requests

## Technology Stack

### Frontend

- HTML5
- CSS3
- Vanilla JavaScript

### Backend

- PHP
- PHP Sessions
- JSON-based HTTP requests

### Database

- MariaDB
- MySQLi

### Server Environment

- Linux
- Apache
- PHP
- MariaDB

## Architecture

The application follows a simple LAMP-based architecture:

┌──────────────────────────────┐
│          Frontend            │
│       HTML / CSS / JS        │
└──────────────┬───────────────┘
               │
               │ HTTP Requests
               │
       ┌───────┴────────┐
       │                │
       ▼                ▼
    JSON API       Multipart Form
       │                │
       ▼                ▼
┌──────────────────────────────┐
│            PHP               │
│                              │
│  login.php                   │
│  register.php                │
│  upload.php                  │
│  delete.php                  │
│                              │
│  auth.php                    │
│  db.php                      │
└──────────────┬───────────────┘
               │
               ▼
┌──────────────────────────────┐
│           MariaDB            │
│                              │
│  users                       │
│  photos                      │
└──────────────────────────────┘
