## 📰 News Aggregator (Laravel Backend)

A backend system built with Laravel that aggregates news from multiple external APIs (NewsAPI, The Guardian, New York Times), stores them locally, and exposes REST API endpoints for search, filtering, and retrieval.

---

## 🚀 Features
- Fetch articles from external sources (NewsAPI, Guardian, New York Times).
- Store articles with relationships (Source, Author, Category).
- Protected API endpoints with Laravel Sanctum authentication.
- Filtering by category, source, author, and date.
- Keyword search functionality.
- Pagination support.
- Unit tests for API endpoints.

---

## ⚙️ Setup Instructions

1) Clone the repository
   ```bash
   git clone https://github.com/zeelvishalmehta/news-aggregator.git
   
   cd news-aggregator

2) Install dependencies
   ```bash
   composer install

3) Copy .env file
   ```bash
   cp .env.example .env

4) Generate application key
   ```bash
   php artisan key:generate

5) Update .env file
   
    - Set your MySQL database credentials
      
    - API keys (NEWS_API_KEY, GUARDIAN_KEY, NYT_KEY)
         ```bash
        NEWSAPI_KEY=Your_API_Key
         
        GUARDIAN_KEY=Your_API_Key
         
        NYT_KEY=Your_API_Key

6) Database setup

   ✅ No DB dump is required.
   
   ✅ Tables and seed data will be created automatically.
   
   ```bash
   php artisan migrate --seed   

8) Fetch articles from external APIs
   ```bash
   php artisan app:fetch-articles

---

## 🔑 Authentication & Token

All API endpoints are protected with Sanctum authentication.

1️⃣ First-time Setup (via Tinker)

    php artisan tinker

Inside Tinker:

    $user = \App\Models\User::create(['name' => 'Test User','email' => 'test@example.com','password' => bcrypt('password'),]);
    $token = $user->createToken('TestToken')->plainTextToken;    
    echo $token;

Use the token in Postman or curl:

    GET http://127.0.0.1:8000/api/articles
    Authorization: Bearer YOUR_TOKEN_HERE
    Accept: application/json

2️⃣ Generate Token via CLI (after user exists)

You can generate a token without Tinker:

    php artisan token:generate {user_id}

Example:

    php artisan token:generate 1

This will output a valid token for the given user.

---

## 📡 API Endpoints

1. Start local server
   ```bash
    php artisan serve

2. Endpoints

    - GET /api/articles → Get paginated articles
      
    Filters supported:
   
    ```bash
    ?source=newsapi
    ?category=sports
    ?author=Adam
    ?date_from=2025-09-01&date_to=2025-09-14
    ```

    - GET /api/articles/{id} → Get a single article by ID

    - GET /api/articles?q=keyword → Search articles by keyword
    

   ⚠️ Without a valid token you’ll get:
    
     {"status":"error","message":"Unauthenticated."}
     

---

## 👤 User Preferences

The system supports soft preferences, allowing each authenticated user to prioritize their content:

- Preferred Sources → e.g., NewsAPI, Guardian

- Preferred Categories → e.g., Sports, Technology

- Preferred Authors → e.g., Adam

## Endpoints

Save / Update Preferences

```bash
POST /api/preferences
Authorization: Bearer <token>
Accept: application/json
```

Request body:

```bash
{
  "preferred_sources": ["newsapi", "guardian"],
  "preferred_categories": ["sports", "technology"],
  "preferred_authors": ["Adam"]
}
```

Response:

```bash
{
  "status": "success",
  "message": "Preferences updated successfully",
  "data": {
    "user_id": 1,
    "preferred_sources": ["newsapi", "guardian"],
    "preferred_categories": ["sports", "technology"],
    "preferred_authors": ["Adam"]
  }
}
```

### Get Preferences

```bash
GET /api/preferences
Authorization: Bearer <token>
```

### Behavior

- Preferred articles are ranked higher in /api/articles.

- Articles outside preferences are still visible → ensuring no content is lost.

- Users can update preferences anytime using the same POST /api/preferences endpoint.


---


## ⚡ Caching

To improve performance and reduce database load, API responses for '/api/articles' are cached.

- Cache driver can be configured in `.env` (default: `file`).
- Each user’s query (filters + search + page + preferences) generates a unique cache key.
- Cache lifetime: **10 minutes**.
  

---


## 🧪 Running Tests

```bash
php artisan test
```

---

## ✅ Notes

- Requires MySQL database.

- If an API is down, fetch will continue for other sources (handled with try-catch).

- Missing values (like description/image) are stored as null.

- Tokens must always be passed in the Authorization: Bearer <token> header.
