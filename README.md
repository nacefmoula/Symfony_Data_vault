# Privacy-Preserving Document Vault

A secure web application built with Symfony 6 that allows users to privately upload, store, and manage documents. Documents are accessible only to their owners unless explicitly shared through secure, time-limited links.

---

## Features

- **User Authentication:** Register and log in to manage your documents securely.
- **Private Document Storage:** Each user can upload files; only they can access their documents.
- **Secure File Uploads:** Files are stored on the server and associated with user accounts.
- **Document Management:** Create, view, download, edit, and delete your documents.
- **Secure Sharing (Optional):** Generate unique, time-limited sharing links for individual documents.
- **Privacy-First:** No user can see or access other users' files unless a sharing link is provided.

---

## Getting Started

### Prerequisites

- PHP >= 8.1
- Composer
- Symfony CLI (optional, but recommended)
- A database (e.g., MySQL, PostgreSQL, or SQLite)

### Installation

1. **Clone the Repository:**
   ```bash
   git clone https://github.com/yourusername/your-repo.git
   cd your-repo
   ```

2. **Install Dependencies:**
   ```bash
   composer install
   ```

3. **Setup Environment Variables:**
   - Copy `.env` to `.env.local` and update database credentials as needed.

4. **Run Database Migrations:**
   ```bash
   php bin/console doctrine:migrations:migrate
   ```

5. **Create Uploads Directory:**
   ```bash
   mkdir -p public/uploads/documents
   chmod -R 775 public/uploads
   ```

6. **Run the Application:**
   ```bash
   symfony server:start
   ```
   or
   ```bash
   php -S localhost:8000 -t public
   ```

---

## Usage

- Register a new account or log in.
- Upload and manage your documents from the dashboard.
- Generate and share secure, time-limited links to share documents (if enabled).
- Only you can view and manage your documents by default.

---

## Project Structure

```
├── src/
│   ├── Controller/
│   ├── Entity/
│   ├── Form/
│   └── Repository/
├── templates/
├── public/
│   └── uploads/documents/
├── migrations/
├── .env
└── README.md
```

---

## Security & Best Practices

- Uploaded files are stored outside the web root or protected with access controls.
- User authentication is required for all document actions.
- Sharing links are cryptographically secure and expire after a set time or number of uses.
- Sensitive data (like `.env` and uploads) are excluded from version control.

---

## License

This project is licensed under the MIT License.

---

## Contributing

Pull requests and issues are welcome! Please open an issue to discuss new features or report bugs.

---

## Roadmap Ideas

- End-to-end encryption for uploaded files
- Document versioning and audit logs
- Two-factor authentication (2FA)
- Advanced sharing controls (password-protected links, role-based access)
- Document previews and tagging

---

## Credits

Built with [Symfony](https://symfony.com/) and inspired by privacy-first cloud storage solutions.
