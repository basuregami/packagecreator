#Package Name: basuregami/packagecreator
	

#Installation 
	
	 1. In order to install packagecreator package, just add the following to your composer.json file. Then run  composer update

	   add this repository to download the package: 

	   		"repositories": [
        		{
            		"type": "git",
            		"url":  "https://github.com/basuregami/packagecreator.git"
        		}
        	]

	   And composer require: "basuregami/packagecreator:dev-master"

	   

     2. Register on the service provider on app.php file inside config folder

     	 Add this on provider  	
       	 	\basuregami\packagecreator\PackageManageServiceProvider::class,
     	 		
 	

 	3. Testing package

 			Artisan Command:-

 					php artisan list

 					#New artisan command will be listed

                         PackageManage
                            PackageManage:new    Package Manage is a laravel package which helps to create boilerplate for creating new package\

                    #Description

                        Package will take two parameter that is vendor name and package name

                            example: php artisan:new basuregami testpackage

                        The commond will create basic scaffold for creating a new package and at the mean time register our new package on main application provider array and in composer.json file so that we can test our package simultaneously will creating. 

				

