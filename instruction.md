##Instruction for first start:

You need to do this scripts in terminal:

1. Clone a project `git clone https://github.com/kano-kenji/knst-test.git`
2. Going to the project's folder and run `composer install`
3. Start the server `symfony server:start`
3. Check `.env` file and make changes for database connection.
`DATABASE_URL=mysql://`{USERNAME}`:`{PASSWORD}`@127.0.0.1:3306/`{DATABASE_NAME}`?serverVersion=8.0`
4. Run these scripts: 
- `bin/console make:migration`
- `bin/console doctrine:migrations:migrate`
- `bin/console doctrine:fixtures:load`
5. Test the request in Postman: 
    - Method: `GET`
    - Request: `http://127.0.0.1:8000/api/users/`