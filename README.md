# Image Hosting and Sharing Service

Image Hosting is a cloud-based image-storage solution that allows you to save files online and access them anywhere from any smartphone, tablet, or computer. You can use Drive on your computer or mobile device to securely upload files and edit them online. Drive also makes it easy for others to edit and collaborate on files.

------

# Documentation

The project is Rest Api's made on **Laravel**, which retrives the data from the user and stores it in the database.



#	First Step

Make a new laravel Project.

    laravel new  project-name

The new laravel project will be created in your system after few minutes, then go in that folder and open it.

    cd project-name

Open project in VS-Code.

    code . 


Then open the project and start working on it.



#	Second Step

* 	Link the project with github so we can upload the changes that  we make changes repeatedly and save it online as a backup.

*	GitHub is a confirmation that our code exists as a backup if we need it again if something goes wrong.

* Go to github and create a new repositiory and then push your project on github so that you can always push your project after updation.

* These are the commands to link project with github.

        git init

        git add . or git add ['filename']

        git commit -m "My first File"

        git remote add origin https://github.com/yourusername/your-repo-name.git

        git push origin master


 
#	Third Step

* Get Done with the database.

* Install the setup for the database if not installed.

* After Installation of Database which will MongoDB, open cmd (Command Prompt) and check if the database has installed in our system or not by typing mongo on cmd.

* If some action (code of lines) is performed, then you have successfully installed mongodb else there was some issue in the installation of mongodb. 

* Then merge the database with the project so further there will be no problems with the database.

* If there are some issue related to this go, see this MongoDB Documentation and video.

* Documentation Link: (https://docs.mongodb.com/drivers/php/).

* Video Link: (https://www.youtube.com/watch?v=TOBqJa2GWQY).




#	Fourth Step

*	Now start developing your project with respect to the requirements.


    ### **Requirements**

    - **Sign Up (profile picture, name, age, password, email)**

    - **Log in**

    - **Profile Update (profile picture, name, age, password, email)**

    - **Forget password**

    - **Upload / Remove Images**

    - **List all photos**

    - **Search photos ( date, time, name, extensions, private, public, hidden)**

    - **Make any photo public/private(by email)/hidden. ps photo will be hidden by default.**

    - **Get a shareable link.**

    - **Deploy it to heroku.**

- Reference: **https://imgbb.com/**


# Implementation 

    REST API'S 

- **Singuip Api.**

- **Email Confirmation Api.**

- **Login Api.**

- **Foret Password Api.**

- **Change Password Api.**

- **Update User Profile Api.**

- **User Upload Image Api.**

- **User Remove Image Api.**

- **User Search Image Api.**

- **Make Image Public Api.**

- **Make Image Hidden Api.**

- **Make Image Private and give Access to Users Api.**

- **Remove Private Image Access Api.**

- **Generate Shareable Link of Image Api.**

- **Show Generated Link Api.**

- **Logout Api.**


#	(1) Signup API

- Signup Requirements (profile picture, name, age, password, email) in form data.

-	Make a Request file for User Signup Request and validate all inputs that are required.

-	Use a model file for User.

-	Make a Controller file for the User.

-	Make middleware for UserSignup Api.

-	Make Service for sending confirmation email.

-	Confirm the email is received.

-	Make a route for Signup Api.

-	Move to next step.

#	(2) Email Verify API

-	Confirm the email and verify the email.

-	Once the email is verified the entity (email_verified_at) will be updated with date and time. 

#	(3) Login API

-	Login Requirements (email, password) in form data.

-	Make a Request file for User Login Request and validate all inputs that are required.

-	Now install JWT Token Services from here (https://github.com/firebase/php-jwt/).

-	composer require firebase/php-jwt.

-	Make a middleware for email verify in login Api.

-	If your email is verified, then user will be logged in else user can’t logged in.

-	After logging in the token will generated and status will be stetted to 1 in the database.

# (4) Forgot Password API

-	Forgot Password Requirements (email) in form data.

-	Make a Request file for User Forgot Password and validate all inputs that are required.

-	Email will be generated with a verification token for forgot password. 

# (5) Change Password API

-	Change Password Requirements (email, otp, new password) in form data.

-	Make a Request file for User Change Password and validate all inputs that are required.

-	Verify the otp and Update User Password.

# (6) User Update Profile API

-	User Update Profile Requirements (jwt_token, profile picture, name, age, password, email) in form data.

-	Make a Request file for User Update Profile and validate only token input that are required, rest are not required due to some reasons of (null values).

-	Update the user credentials which are provided.

# (7) Upload Photo API
-	Upload Photo Requirements (jwt_token, user_id, photo/image, access (hidden, public, private)) in form data.

-	Make a Request file for User Upload and validate all inputs that are required.

-	Get image in form of encoded base64 and then decode and get credentials (extension and path with http server).

-	Photo will be uploaded in databases.

-	By default, image access type will be hidden.

# (8) Delete / Remove Photo Api
-	Remove Photo Requirements (jwt_token, user_id, photo/image) in form data.

-	Make a Request file for User Remove and validate all inputs that are required.

-	Photo will be remove / deleted from databases.

# (9) User Search API

-	User Search Requirements (jwt_token, user_id, date, time, image_name, image_extension, image_accessors) in form data.

-	Make a Request file for User Search Photo and validate all inputs that are required.

-	The searched photo can be searched on requirement basis.

-	(jwt_token, user_id, date, time, image_name, image_extension, image_accessors).

# (10) User Make Photo Public API

-	User Make Photo Public Requirements (jwt_token, user_id, photo_id, access(public)) in form data.

-	Make a Request file for User Search Photo and validate all inputs that are required.

-	The photo access type will be converted to public from (hidden / private).

-	The photo be updated in database.

# (11) User Make Photo Hidden Again API

-	User Make Photo Public Requirements (jwt_token, user_id, photo_id, access(public)) in form data.

-	Make a Request file for User Search Photo and validate all inputs that are required.

-	The photo access type will be converted to hidden from by (public / private).

-	The photo be updated in database.

# (12) User Make Photo Private API

-	User Make Photo Private Requirements (jwt_token, user_id, photo_id, access (private), email (of users to access private photo)) in form data.

-	Make a Request file for User Search Photo and validate all inputs that are required.

-	The photo access type will be converted to private from by (hidden / public).

-	The photo be updated in database.

# (13) User Remove Email Access of Private Photo API

-	User Remove Email Access of Private Photo Requirements (jwt_token, user_id, photo_id, access (private), email (of user to remove access private photo)) in form data.

-	Make a Request file for User Search Photo and validate all inputs that are required.

-	The email will be removed for private photo access.

-	The photo be updated in database.

# (14) User Generate Link of Photo API

-	User Generate Link of Photo Requirements (jwt_token, photo_id) in form data.

-	Make a Request file for User Search Photo and validate all inputs that are required.

-	The user_id and photo access type will be get from the backed to match. 

-	Then the link will be generated.

# (15)	User Show Generated Link of Photo API

-	User Show Generate Link of Photo Requirements (jwt_token, photo_link) in form data.

-	Make a Request file for User Search Photo and validate all inputs that are required.

-	All conditions will be checked by the access type (hidden, public, private) on that the picture will be viewed. 

# (16) User Logout API

-	User Logout Requirements (jwt_token) in form data.

-	User will be logout and the token and status will set to null and 0.

# MongoDB Atlas Culster Connection

- Here is the link to the introduction of MongoDB,

    - https://docs.mongodb.com/manual/introduction/

- Here is ths link to connect your project with mongodb where your database will be working on a love cluster.

- Here is the detailed document for **MongoDB Atlas** to connect your project.

    - https://www.mongodb.com/compatibility/mongodb-laravel-intergration


# Deployment on Heroku 

- Heroku is a cloud platform as a service supporting several programming languages. One of the first cloud platforms, Heroku has been in development since June 2007, when it supported only the Ruby programming language, but now supports Java, Node.js, Scala, Clojure, Python, PHP, and Go.

- Here is the documentation for deploying app or website on heroku,

    - https://devcenter.heroku.com/categories/reference

- First download the Heroku CLI Installer on your system.

    - https://devcenter.heroku.com/articles/heroku-cli

- Get started to deploy your app / website,

    - https://devcenter.heroku.com/

- Create an App on heroku, and then start deploying your project on heroku platform.

    -    https://devcenter.heroku.com/articles/getting-started-with-php

    -   https://devcenter.heroku.com/articles/git

    -   https://devcenter.heroku.com/articles/procfile

    -   https://devcenter.heroku.com/articles/config-vars

# Deployed API's Link

- **Signup  API : https://imagehosting10.herokuapp.com/user/signup**

- **Email Verification  API : https://imagehosting10.herokuapp.com/user/welcome_login**

- **Login  API : https://imagehosting10.herokuapp.com/user/login**

- **Fogot Password  API : https://imagehosting10.herokuapp.com/user/forget_password**

- **Change Password  API : https://imagehosting10.herokuapp.com/user/change_password**

- **Update Profile  API : https://imagehosting10.herokuapp.com/user/user_update_profile**

- **Upload Image  API : https://imagehosting10.herokuapp.com/photo/user_upload_photo**

- **Remove / Delete Image  API : https://imagehosting10.herokuapp.com/photo/user_delete_photo**

- **Search Images  API : https://imagehosting10.herokuapp.com/photo/user_search_photo**

- **Make Image Public  API : https://imagehosting10.herokuapp.com/photo/make_photo_public**

- **Make Image Hidden  API : https://imagehosting10.herokuapp.com/photo/make_photo_hidden**

- **Make Image Private and give Access  API : https://imagehosting10.herokuapp.com/photo/make_photo_private**

- **Remove Private Image Access  API : https://imagehosting10.herokuapp.com/photo/remove_photo_private_email**

- **Generate Image Shareable Link  API : https://imagehosting10.herokuapp.com/photo/get_a_shareable_link**

- **Show Generated Link  API : https://imagehosting10.herokuapp.com/photo/show_link**

- **Logout API : https://imagehosting10.herokuapp.com/user/logout**