<?php
/**
 * Created by PhpStorm.
 * User: basu
 * Date: 12/13/17
 * Time: 3:25 PM
 */

namespace basuregami\packagecreator;

use ZipArchive;
use RuntimeException;
use GuzzleHttp\Client;
use Illuminate\Filesystem\Filesystem;


class PackageManageHelper
{
    protected $files;

    public function __construct(Filesystem $files)
    {
        $this->files = $files;
    }

    /*Using symfony to setup the custom bar */
    public function barSetup($progress){
        // the finished part of the bar
        $progress->setBarCharacter('<comment>=</comment>');

        // the unfinished part of the bar
        $progress->setEmptyBarCharacter(' ');

        // the progress character
        $progress->setProgressCharacter('|');

        // the bar width
        $progress->setBarWidth(50);

        //setformat
        $progress->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');

        return $progress;
    }


    /**
     * Check if the package already exists.
     *
     * @param  string $defaultPath   Path to the package directory
     * @param  string $vendor The vendor
     * @param  string $name   Name of the package
     *
     * @return void          Throws error if package exists, aborts process
     */
    public function checkPackageExist($defaultPath, $vendor, $name)
    {
        if (is_dir($defaultPath.$vendor.'/'.$name)) {
            throw new RuntimeException('Package you are trying to create already exists');
        }
    }

    /**
     * Create a directory if it doesn't exist.
     *
     * @param  string $path Path of the directory to make
     *
     * @return void
     */
    public function makePackageDir($defaultPath)
    {
        if (!is_dir($defaultPath)) {
            return mkdir($defaultPath, 0777, true);
        }
    }

    /**
     * Download the temporary Zip to the given file.
     *
     * @param  string  $zipFile
     * @param  string  $source
     *
     * @return $this
     */
    public function download($zipFile, $source)
    {
        $client = new Client(['verify' => env('CURL_VERIFY', true)]);
        $response = $client->get($source);
        file_put_contents($zipFile, $response->getBody());
        return $this;
    }



    /**
     * Generate a random temporary filename for the package zipfile.
     *
     * @return string
     */
    public function makeFilename()
    {
        return getcwd().'/package'.md5(time().uniqid()).'.zip';
    }


    /**
     *
     * @param  string $oldFile The haystack
     * @param  mixed  $search  String or array to look for (the needles)
     * @param  mixed  $replace What to replace the needles for?
     * @param  string $newFile Where to save, defaults to $oldFile
     *
     * @return void
     */
    public function replaceAndSaveFile($oldFile, $search, $replace, $newFile = null)
    {
        $newFile = ($newFile == null) ? $oldFile : $newFile;
        $file = $this->files->get($oldFile);
        $replacing = str_replace($search, $replace, $file);
        $this->files->put($newFile, $replacing);
    }

    /**
     * Extract the zip file into the given directory.
     *
     * @param  string  $zipFile
     * @param  string  $directory
     *
     * @return $this
     */
    public function extract($zipFile, $directory)
    {
        $archive = new ZipArchive;
        $archive->open($zipFile);
        $archive->extractTo($directory);
        $archive->close();
        return $this;
    }

    /**
     * Clean-up the Zip file.
     *
     * @param  string  $zipFile
     *
     * @return $this
     */
    public function cleanUp($zipFile)
    {
        @chmod($zipFile, 0777);
        @unlink($zipFile);
        return $this;
    }

    /**
     * New composer instance that dumps autoloads.
     *
     * @return mixed
     */
    public function dumpAutoloads()
    {
        //return $this->composer->dumpAutoloads();
        shell_exec('composer dump-autoload');
    }


}