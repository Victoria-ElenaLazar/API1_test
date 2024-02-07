# API_test

## Installation and Setup

1. **Clone the Repository:**

    ```bash
    git clone https://github.com/Victoria-ElenaLazar/API_test
    ```

2. **Install Dependencies:**

   Navigate to the project directory and install the dependencies using Composer:

    ```bash
    composer install
    ```

## 1 - CLI Command to Fetch Users from APIs

We have two APIs that provide lists of users through a CLI command that fetches users from those endpoints and saves user information into a CSV file.

### Endpoints

First API:
- URL: https://run.mocky.io/v3/03d2a7bd-f12f-4275-9e9a-84e41f9c2aae
- Method: GET
- Query string: `?verbose=1`

Second API:
- URL: https://run.mocky.io/v3/aab281fe-3dbb-4d91-a863-a96e6bf083d7
- Method: GET
- Query string: `?expose=1`

### Requirements:
- Use PHP 8.2.
- Write tests for the CLI command.

### Usage:
1. Run the CLI command to fetch users and save them into a CSV file:

    ```bash
    symfony console app:fetch:users
    ```

## 2- CLI Command for Searching Users with Specific Tags

Search users that have specific tags inside the created CSV file.
When a user is found with the given tags, its name and salary should be written to the output as a list.

### Usage:
1. Run the CLI command to search for users with specific tags:

    ```bash
    symfony console app:search-users --tag=cjohnson
    ```

**Important Note**: The links provided return 404 errors. 
Therefore, I created a CSV file in the root directory (`data/users.csv`) to ensure the data is read correctly.
