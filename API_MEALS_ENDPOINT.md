# Meal API Endpoint Documentation

## Get All Meals Endpoint

### Endpoint URL
```
GET /api/v1/meals
```

### Full URL Examples
- Local: `http://localhost/api/v1/meals`
- With port: `http://localhost:8000/api/v1/meals`
- Production: `https://yourdomain.com/api/v1/meals`

### Authentication
Currently, the API routes don't require authentication. If you need to add authentication later, you can use Sanctum tokens obtained from:
```
POST /api/login
Body: {
    "email": "user@example.com",
    "password": "password"
}
```

### Response Format
The API returns a JSON collection with all meal data including images:

```json
{
    "data": [
        {
            "id": 1,
            "title": "grill chicken",
            "description": "Premium subscription plan with all features",
            "category_id": 1,
            "category": {
                "id": 1,
                "name": "lunch"
            },
            "category_name": "lunch",
            "calories": 23,
            "protein_g": 12,
            "fat_g": 12,
            "carbs_g": 33,
            "extras": "23",
            "is_active": 1,
            "type": "is meal",
            "image": {
                "id": 1,
                "url": "http://localhost/storage/meals/1/image/meal_1_1234567890.jpg",
                "thumb_url": "http://localhost/storage/meals/1/image/conversions/thumb-meal_1_1234567890.jpg",
                "preview_url": "http://localhost/storage/meals/1/image/conversions/preview-meal_1_1234567890.jpg",
                "file_name": "meal_1_1234567890.jpg",
                "mime_type": "image/jpeg",
                "size": 123456
            },
            "image_url": "http://localhost/storage/meals/1/image/meal_1_1234567890.jpg",
            "image_thumb_url": "http://localhost/storage/meals/1/image/conversions/thumb-meal_1_1234567890.jpg",
            "created_at": "2025-11-18 06:47:18",
            "updated_at": "2025-11-18 06:47:18",
            "deleted_at": null
        }
    ]
}
```

### Postman Setup

1. **Method**: GET
2. **URL**: `http://localhost/api/v1/meals` (adjust based on your environment)
3. **Headers**: 
   - `Accept: application/json`
   - `Content-Type: application/json`
   - (Optional) `Authorization: Bearer {token}` if authentication is added

### Other Available Meal Endpoints

- **Get Single Meal**: `GET /api/v1/meals/{id}`
- **Create Meal**: `POST /api/v1/meals`
- **Update Meal**: `PUT /api/v1/meals/{id}`
- **Delete Meal**: `DELETE /api/v1/meals/{id}`
- **Get Categories**: `GET /api/v1/meals/categories`
- **Upload Media**: `POST /api/v1/meals/media`

### Image URLs
All image URLs are fully qualified and include:
- **url**: Full-size image
- **thumb_url**: Thumbnail (50x50px)
- **preview_url**: Preview size (120x120px)

If thumb or preview conversions don't exist, they fall back to the original image URL.


