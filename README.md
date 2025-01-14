# Spotify Account Management App

This is a simple web application built with CakePHP and integrated with the Spotify API. The application allows users to manage their Spotify accounts. Due to Spotify API limitations, only pre-authorized test users can log in to the app.

## Live Demo
You can test the application at the following link:

[Live Demo](https://szymongalaska.publicvm.com)

To log in as a test user, click the **"Login as test user"** button. 

### Spotify Test Account Credentials
If needed, use the following credentials to log in to the Spotify test account:

- **Email**: `uqi54786@msssg.com`
- **Password**: `h01=;/8E=l[;`

## Installation

To run this application locally, follow the steps below:

### Prerequisites
- PHP 8.3 or higher.
- Composer installed.
- A database (MySQL, MariaDB, or any supported by CakePHP).

### Steps

1. **Clone the Repository**
   ```bash
   git clone https://github.com/szymongalaska/spotify
   cd spotify
   ```

2. **Install Dependencies**
   ```bash
   composer install
   ```

3. **Configure the Application**
   - Copy the `config/.env.default` file to `config/.env`:
     ```bash
     cp config/.env.default config/.env
     ```
   - Edit the `.env` file and add your database and Spotify API credentials:
     ```env

     SPOTIFY_CLIENT_ID="your_spotify_client_id"
     SPOTIFY_CLIENT_SECRET="your_spotify_client_secret"
     SPOTIFY_REDIRECT_URI="http://your-local-url/callback"
     ```

4. **Generate API Keys from Spotify**
   - Go to the [Spotify Developer Dashboard](https://developer.spotify.com/dashboard/).
   - Create a new application.
   - Add your redirect URI (`http://your-local-url/callback`) to the list of redirect URIs.
   - Copy the **Client ID** and **Client Secret** into the `.env` file.

5. **Run Migrations**
   ```bash
   bin/cake migrations migrate
   ```

6. **Start the Application**
   ```bash
   bin/cake server
   ```
   The application will be available at `http://localhost:8765`.

## Notes
- The Spotify API requires users to be pre-authorized. This is why the application includes a "Login as test user" button.
- You may need to whitelist the callback URL in your Spotify developer application settings.

## License
This project is licensed under the MIT License.