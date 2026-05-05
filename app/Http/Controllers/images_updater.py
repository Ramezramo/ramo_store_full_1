# import requests
# import os

# # Laravel endpoint URL (adjust to your actual Laravel route)
# LARAVEL_URL = 'http://localhost/api/v2/products-images/49'

# # Directory containing your images
# IMAGE_DIR = 'path/to/your/images'

# def upload_images_to_laravel():
#     # Sample data structure with images and their colors
#     images_with_colors = {
    
#         "red": "D:/xammp/htdocs/ramostore/api-ramo-store-lara/app/Http/Controllers/20250122_025333.png"
#     ,
    
#         "blue": "D:/xammp/htdocs/ramostore/api-ramo-store-lara/app/Http/Controllers/ramologo.png"
    
#     }

#     # Prepare files for multipart/form-data
#     files = {}
#     for color, image_filename in images_with_colors.items():
#         image_path = os.path.join(IMAGE_DIR, image_filename)
#         if os.path.exists(image_path):
#             files[f'selectedImagesWithColors[{color}]'] = (
#                 image_filename,
#                 open(image_path, 'rb'),
#                 'image/jpeg'  # Adjust MIME type based on your file types
#             )
#         else:
#             print(f"Image not found: {image_path}")

#     try:
#         # Send POST request to Laravel endpoint
#         response = requests.post(LARAVEL_URL, files=files)

#         # Check response
#         if response.status_code == 200:
#             print("Upload successful!")
#             print("Response:", response.json())
#         else:
#             print(f"Upload failed with status code: {response.status_code}")
#             print("Response:", response.text)

#     except requests.exceptions.RequestException as e:
#         print(f"Error during request: {str(e)}")

#     finally:
#         # Close all file handles
#         for file_data in files.values():
#             file_data[1].close()

# if __name__ == '__main__':
#     upload_images_to_laravel()

import requests
import json
import os

# API endpoint configuration
ENDPOINT = "http://localhost/api/v2/products-images/48"  # Replace with your endpoint




# Headers (include authentication if required)
headers = {
    "Accept": "application/json",
    # Uncomment and add token if your API requires authentication
    # "Authorization": "Bearer your-api-token"
}

# List of image paths to upload (add as many as needed)
image_paths = [
    "D:/xammp/htdocs/ramostore/api-ramo-store-lara/app/Http/Controllers/20250122_025333.png"
    ,
     "D:/xammp/htdocs/ramostore/api-ramo-store-lara/app/Http/Controllers/ramologo.png"
   
    # Add more image paths here
]

# Files dictionary to hold all uploads
files = {}

# Validate and prepare all files
for i, image_path in enumerate(image_paths):
    # Verify file exists and is readable
    if not os.path.exists(image_path):
        print(f"Error: File does not exist at {image_path}")
        continue
    if not os.access(image_path, os.R_OK):
        print(f"Error: File at {image_path} is not readable")
        continue

    # Add file to request with array notation 'otherImages[]'
    try:
        files[f'naturalImages[{i}]'] = (os.path.basename(image_path), open(image_path, 'rb'), 'image/*')
        print(f"Added file: {image_path}")
    except IOError as e:
        print(f"Error: Could not open file {image_path}: {e}")
        continue

# Check if we have any valid files to upload
if not files:
    print("Error: No valid files to upload")
    exit(1)

try:
    # Send POST request with files (using POST since we're uploading multiple files)
    print(f"Sending POST request to {ENDPOINT}")
    response = requests.post(ENDPOINT, files=files, headers=headers)

    # Check response status
    if response.status_code == 200:
        print("Success! Images updated successfully.")
        print(response.text)
    else:
        print(f"Failed with status code: {response.status_code}")
        try:
            print(json.dumps(response.json(), indent=2))
        except json.JSONDecodeError:
            print(response.text)

except requests.exceptions.RequestException as e:
    print(f"Request error occurred: {e}")
except ValueError as e:
    print(f"JSON decode error: {e}")
finally:
    # Close all file handles
    for file_data in files.values():
        file_data[1].close()  # Close the file object (second element of tuple)
    print("File handles closed.")