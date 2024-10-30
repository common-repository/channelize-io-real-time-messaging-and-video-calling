<?php
/**
 * Channelize Compressor Class
 *
 * @link       https://channelize.io/
 * @package Channelize
 * @since      2.0.0
 */

defined( 'ABSPATH' ) || exit;

/** Initialize the class and set its properties **/
class WP_Channelize_Compressor {

	/**
	 * Compress a folder (including itself) - Folder path that should be Tar.
	 *
	 * @param string $source_path       Required Relative path of directory to be Tar.
	 * @param string $destination_path  Required Path of output tar file.
	 */
	public static function create_tar( $source_path, $destination_path ) {
		$path_info      = pathinfo( $source_path );
		$parent_path    = $path_info['dirname'];
		$directory_name = $path_info['basename'];

		$z = new ZipArchive();
		$z->open( $destination_path, ZipArchive::CREATE );
		if ( $source_path === $directory_name ) {
			self::dir_to_tar( $source_path, $z, 0 );
		} else {
			self::dir_to_tar( $source_path, $z, strlen( "$parent_path/" ) );
		}
		$z->close();

		return true;
	}

	/**
	 * Add files and sub-directories in a folder to zip files.
	 *
	 * @param string $directory          Required Folder path that should be zipped.
	 * @param string $zip_file           Required Zip file where files end up.
	 * @param int    $exclusive_length   Required Number of text to be excluded from the file path.
	 */
	private static function dir_to_tar( $directory, &$zip_file, $exclusive_length ) {
		$handle = opendir( $directory );
		while ( false !== $file = readdir( $handle ) ) {
			// Check for local/parent path or zipping file itself and skip. && basename( __FILE__ ) !== $f.
			if ( '.' !== $file && '..' !== $file ) {

				$file_path = "$directory/$file";

				// Remove prefix from file path before add to zip.
				$local_path = substr( $file_path, $exclusive_length );
				if ( is_file( $file_path ) ) {
					$zip_file->addFile( $file_path, $file );
				} elseif ( is_dir( $file_path ) ) {
					// Add sub-directory.
					$zip_file->addEmptyDir( $local_path );
					self::dir_to_tar( $file_path, $zip_file, $exclusive_length );
				}
			}
		}
		closedir( $handle );
	}


}
