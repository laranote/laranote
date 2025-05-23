# Laranote

A Laravel and InertiaJS application that enables rich-text note writing and real-time collaboration.

## Overview

Laranote is a powerful note-taking application that allows users to create, edit, and collaborate on rich-text documents in real-time. Built with Laravel and InertiaJS, it leverages the TipTap editor for a seamless writing experience.

## Key Features

- **Rich Text Editor** powered by TipTap with:
  - Tables
  - Lists
  - Code Blocks
  - Emoji support
  - And many more extensions...
- **Real-time Collaborative Editing**
  - Multiple users can edit documents simultaneously
  - Changes sync instantly across all connected clients
  - Conflict-free editing with CRDT-based synchronization
- **Autosave Functionality**
- **File Attachments**
- **User Role Management**

## Technology Stack

- **Backend**: Laravel Framework v11.31.x
- **Frontend**: InertiaJS with Vue.js
- **Rich Text Editor**: TipTap
- **Collaborative Editing**: Hocuspocus and Y.js
- **Real-time Communication**: WebSockets

## Collaborative Editing

Laranote implements real-time collaborative editing using:

- **Tiptap**: A rich text editor framework based on ProseMirror
- **Hocuspocus**: A collaborative editing server that synchronizes document changes
- **Y.js**: A CRDT implementation that enables conflict-free document editing

The Hocuspocus server is configured to work directly with a MySQL database. It reads from and writes to the database in real-time, eliminating the need for webhooks. This direct database integration ensures efficient and reliable synchronization of document changes across all connected clients.


## Installation

**Requirements:**

- Laravel Framework v11.31.x
- PHP v8.3
- Node.js (for Hocuspocus server)
- MySQL database (required for Hocuspocus server)

```bash
git clone git@github.com:digitalnodecom/laranote.git
cd laranote
composer install
npm install
```

Make sure you create and configure a .env file following the .env.example (magic.mk instructions below).
You can generate an app key with:

```bash
php artisan key:generate
```

Configure your MySQL database connection in the .env file.
The Hocuspocus server **requires MySQL to function properly**.
Then run:

```bash
php artisan migrate
php artisan storage:link
```

## Running the Application

You can run the project with:

```bash
composer dev #runs "npm run dev", "php artisan serve", "node hocuspocus-server/hocuspocus.js",
```

This command starts:
1. The Laravel development server
2. The Vite development server for frontend assets
3. The Hocuspocus collaborative editing server

## Hocuspocus Server Configuration
By default, we connect via WebSocket to the Hocuspocus server on a specific port defined in the environment:

```
HOCUSPOCUS_LARAVEL_PORT=1234
```

In a production setup, especially behind a reverse proxy like Nginx or a load balancer, it's common to avoid exposing ports directly. Instead, you can route WebSocket traffic through a specific path. To support this, you can set the following in your .env:

```
HOCUSPOCUS_REVERSE_PROXY_ROUTE=/your-proxy-path
```

When this value is set, the application constructs the WebSocket URL using that route instead of a raw port number. This makes it easier to work with proxies and improves both compatibility and security.

The application also automatically adapts to the protocol based on your APP_URL:

* If you're running over https, it uses the secure wss:// (WebSocket Secure).
* If you're running over http, it falls back to ws://.

This ensures the WebSocket connection matches the security level of your main application.

Examples:
* Without reverse proxy: wss://yourdomain.com:1234
  * If you're running over http: ws://yourdomain.com:1234
* With reverse proxy: wss://yourdomain.com/your-proxy-path
  * If you're running over http: ws://yourdomain.com/your-proxy-path

## Maintenance

There is a custom command that removes unused files from "public/storage/uploads". 
It's defined in console.php, so in production, you need to set up "php artisan schedule:run" 
to run every 2 minutes (as specified in the docs).

```bash
php artisan app:cleanup-unused-files
```

## Code Quality

Before pushing code, we recommend running code analysis.
We use [Larastan](https://github.com/larastan/larastan) for static analysis:

```bash
./vendor/bin/phpstan analyse
# or if it fails:
./vendor/bin/phpstan analyse --memory-limit=2G
```

## Authentication Options

### Magic.mk Magic Login Setup

1. Register on magic.mk
2. Create a project
3. Copy and paste the project's key (slug) when initializing the project
4. Copy and paste the project's API key when initializing the project

### Standard Login Setup

The application also supports standard email/password authentication using a JSON file-based user system:

1. Create a `.users` file in the root directory with your users' credentials:
```json
[
    {
        "email": "user@example.com",
        "password": "hashed_password"
    }
]
```

2. Passwords must be hashed using PHP's password_hash() function. You can use the following command to generate a hash:
```php
php -r "echo password_hash('your_password', PASSWORD_DEFAULT);"
```

3. Default test credentials:
   - Email: admin@example.com
   - Password: password

The authentication system will automatically create or retrieve a user record in the database when valid credentials are provided.

## Dependencies

- Laravel Framework v11.31.x
- PHP v8.3
- Node.js and npm
- MySQL database
