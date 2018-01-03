<?php
/**
 * Created by PhpStorm.
 * User: basu
 * Date: 12/13/17
 * Time: 1:51 PM
 */

namespace olivemediapackage\PackageManage;
use Illuminate\Console\Command;
use olivemediapackage\PackageManage\PackageManageHelper;


class PackageManageNewCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'PackageManage:new {vendor} {packagename} {--i}';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Package Manage is a laravel package which helps to create boilerplate for creating new package';


    /**
     * PackageMange Helper class
     * @var object
     * */
    protected $helper;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(PackageManageHelper $helper)
    {
        parent::__construct();
        $this->helper = $helper;
    }


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //using laravel default function to create the progresbar
        $progress = $this->helper->barSetup($this->output->createProgressBar(7));
        $progress->start();


        if ($this->option('i')) {
            $vendor = $this->ask('Define the vendor name for your package?', $this->argument('vendor'));
            $name = $this->ask('Type your package Name?', $this->argument('packagename'));
        } else {
            $vendor = $this->argument('vendor');
            $name = $this->argument('packagename');
        }

        $progress->advance();

        // Directory path creation
        // All the New package will reside under "Pacakges" folder
        $defaultPath = getcwd().'/Packages/';
        $finalPath = $defaultPath . $vendor .'/' . $name;

        //composer json file require settings
        $requireSupport = '"Illuminate/support": "~5",
        "php"';
        $requirement = '"psr-4": {
            "'.$vendor.'\\\\'.$name.'\\\\": "Packages/'.$vendor.'/'.$name.'/src",';
        $appConfigLine = 'App\Providers\RouteServiceProvider::class,
        '.$vendor.'\\'.$name.'\\'.$name.'ServiceProvider::class,';

        // Start creating the package
        $this->info('Creating package '.$vendor.'\\'.$name.'...');
        $this->helper->checkPackageExist($defaultPath, $vendor, $name);
        $progress->advance();


        // Create the package directory
        $this->info('Creating packages directory...');
        $this->helper->makePackageDir($defaultPath);
        $progress->advance();


        // Create the vendor directory
        $this->info('Creating vendor...');
        $this->helper->makePackageDir($defaultPath.$vendor);
        $progress->advance();


        //create the workspace for the package
        //download the skleton repo from the php league and cleanup and create workspace for package
        $this->info('Downloading skeleton And Creating Workspace for Package...');
        $this->helper->download($zipFile = $this->helper->makeFilename(), 'http://github.com/thephpleague/skeleton/archive/master.zip')
            ->extract($zipFile, $defaultPath.$vendor)
            ->cleanUp($zipFile);
        rename($defaultPath.$vendor.'/skeleton-master', $finalPath);
        $progress->advance();


        // Creating a Service Provider in the src directory
        $this->info('Creating service provider Class...');
        $newProvider = $finalPath.'/src/'.$name.'ServiceProvider.php';
        $this->helper->replaceAndSaveFile(
            \Config::get('packager.service_provider_stub', __DIR__.'/ServiceProvider.stub'),
            ['{{vendor}}', '{{name}}'],
            [$vendor, $name],
            $newProvider
        );
        $progress->advance();


        // Replacing skeleton placeholders
        $this->info('Replacing skeleton placeholders...');
        $this->helper->replaceAndSaveFile($finalPath.'/src/SkeletonClass.php', 'namespace League\Skeleton;', 'namespace '.$vendor.'\\'.$name.';');
        $search =   [
            ':vendor',
            ':package_name',
            ':vendor\\\\:package_name\\\\',
            ':vendor/:package_name',
            'thephpleague/:package_name',
            'league/:package_name',
            '"php"',
            'League\\\\Skeleton\\\\',
            'League\\\\Skeleton\\\\Test\\\\'
        ];
        $replace =  [
            $vendor,
            $name,
            $vendor.'\\\\'.$name.'\\\\',
            $vendor.'/'.$name,
            $vendor.'/'.$name,
            $vendor.'/'.$name,
            $requireSupport,
            $vendor.'\\\\'.$name.'\\\\',
            $vendor.'\\\\'.$name.'\\\\Test\\\\'
        ];
        $this->helper->replaceAndSaveFile($finalPath.'/composer.json', $search, $replace);
        if ($this->option('i')) {
            $this->interactiveReplace($vendor, $name, $finalPath);
        }
        $progress->advance();

        // psr-4 autoloading of the package created to the main application composer.json
        $this->info('Adding package to composer and app...');
        $this->helper->replaceAndSaveFile(getcwd().'/composer.json', '"psr-4": {', $requirement);

        // And add it to the providers array in config/app.php
        $this->helper->replaceAndSaveFile(getcwd().'/config/app.php', 'App\Providers\RouteServiceProvider::class,', $appConfigLine);
        $progress->advance();

        // Finished creating the package, end of the progress bar
        $progress->finish();

        $this->info('Package created successfully!');
        $this->output->newLine(2);
        $progress = null;

        // Composer dump-autoload to identify new MyPackageServiceProvider
        $this->helper->dumpAutoloads();

    }

    protected function interactiveReplace($vendor, $name, $fullPath)
    {
        $author = $this->ask('Who is the author?', \Config::get('packager.author'));
        $authorEmail = $this->ask('What is the author\'s e-mail?', \Config::get('packager.author_email'));
        $authorSite = $this->ask('What is the author\'s website?', \Config::get('packager.author_site'));
        $description = $this->ask('How would you describe the package?');
        $license = $this->ask('Under which license will it be released?', \Config::get('packager.license'));
        $homepage = $this->ask('What is going to be the package website?', 'https://github.com/'.$vendor.'/'.$name);

        $search =   [
            ':author_name',
            ':author_email',
            ':author_website',
            ':package_description',
            'MIT',
            'https://github.com/'.$vendor.'/'.$name,
        ];
        $replace =  [
            $author,
            $authorEmail,
            $authorSite,
            $description,
            $license,
            $homepage,
        ];
        $this->helper->replaceAndSaveFile($fullPath.'/composer.json', $search, $replace);
    }

}