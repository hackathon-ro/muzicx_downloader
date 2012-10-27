<?php

class vlabSyncCommand extends ConsoleCommand {

    public function actionRunFtp() {
        // set up basic connection
        $conn = ftp_connect($this->server);
        if (!$conn) {
            ConsoleCommand::log('vlabSyncRun', 'DEBUG', "Could not connect to " . $this->server);
            return false;
        }
        ConsoleCommand::log('vlabSyncRun', 'DEBUG', "Connected to " . $this->server);

        // login with username and password
        $login = ftp_login($conn, $this->username, $this->password);
        if (!$login) {
            ConsoleCommand::log('vlabSyncRun', 'DEBUG', "Could not connect as " . $this->username);
            return false;
        }
        ConsoleCommand::log('vlabSyncRun', 'DEBUG', "Connected as " . $this->username . "@" . $this->server);

        // get contents of the current directory
        $files = ftp_nlist($conn, ".");

        // output $contents
        var_dump($contents);

        // close this connection
        ftp_close($conn);
    }

    public function actionRunSSH() {
        $processID = uniqid(time());

        $vlab = new vlabSyncHelper();

        //  connect to server
        try {
            $conn = $vlab->connect();
        } catch (Exception $e) {
            ConsoleCommand::log('vlabSyncCommand', 'DEBUG', "Connection exception: " . $e->getMessage());
            return false;
        }

        if (!$conn) {
            ConsoleCommand::log('vlabSyncCommand', 'DEBUG', "Connection failed");
            return false;
        } else
            ConsoleCommand::log('vlabSyncCommand', 'DEBUG', "Connection successfully");


        //  authenticate to server
        if (!$vlab->authenticate()) {
            ConsoleCommand::log('vlabSyncCommand', 'DEBUG', "Authentication failed");
            return false;
        } else
            ConsoleCommand::log('vlabSyncCommand', 'DEBUG', "Authentication successfully");


        //  get all the files from the vlab upload directory
        $files = $vlab->getFiles($directory = VLAB_REMOTE_HASHES_TMP, $prefix = '', $sufix = '*.dat');
        if (!$files) {
            ConsoleCommand::log('vlabSyncCommand', 'DEBUG', "No vlab uploaded files found");
            return false;
        }

        //  go through all the files
        //  and move them to a temporary folder, from where they will be processed
        //  the name of the destination files will contain a random prefix
        //  to make a difference between the temporary processed files by multiple threads
        foreach ($files as $file) {
            $remoteFileName = basename($file);
            $source = VLAB_REMOTE_HASHES_TMP . "/{$remoteFileName}";
            $destination = VLAB_REMOTE_HASHES_PROCESSING . "/" . "{$processID}_{$remoteFileName}";
            ConsoleCommand::log('vlabSyncCommand', 'DEBUG', "Moving {$source} to {$destination}");
            $moved = $vlab->moveFile($source, $destination);
        }

        //  get all the files from the temporary processing directory
        $files = $vlab->getFiles($directory = VLAB_REMOTE_HASHES_PROCESSING, $prefix = $processID, $sufix = '*.dat');
        if (!$files) {
            ConsoleCommand::log('vlabSyncCommand', 'DEBUG', "No files to process found");
            return false;
        }

        //  the correct files with the proper name and content
        $correctFiles = array();

        //  go through all the files
        foreach ($files as $file) {
            $remoteFileName = basename($file);

            //  download the remote file on the local machine
            $localFile = VLAB_SYNC_DIR . $remoteFileName;
            ConsoleCommand::log('vlabSyncHelper', 'DEBUG', "downloadFile from {$file} to {$localFile}");
            $getFile = $vlab->downloadFile($file, $localFile);
            if (!$getFile) {
                ConsoleCommand::log('vlabSyncHelper', 'DEBUG', "Could not get the file {$file}");
                continue;
            }

            //  check the name and the file content
            ConsoleCommand::log('vlabSyncHelper', 'DEBUG', "Checking file {$localFile}, with pid {$processID}");
            $checked = $vlab->checkFile($localFile, $processID);
            if (!$checked) {
                //  move the file back to the vlab upload directory
                $originalFileName = str_replace("{$processID}_", '', basename($remoteFileName));
                $moved = $vlab->moveFile(VLAB_REMOTE_HASHES_PROCESSING . "/{$remoteFileName}", VLAB_REMOTE_HASHES_TMP . "/{$originalFileName}");
                continue;
            }

            $correctFiles[$file] = $localFile;
        }

        $numFiles = 0;

        $dbTmp = Config::value('WEBUI_DB_TMP');

        //  go through all the correct files
        foreach ($correctFiles as $remoteFile => $localFile) {
            $originalFileName = str_replace("{$processID}_", '', basename($localFile));

            //  delete the temporary table if exists
            //  and create a new fresh one
            $table = "{$dbTmp}.hashes_vlabsync_{$processID}_{$numFiles}_tmp";
            DBHelper::dropTempTable($table);
            DBHelper::createTempTable($table, HashesCass::TMP_TABLE_VLAB_SYNC);

            //  load the file content in MySQL table
            $count = $vlab->loadFile($table, $localFile);
            $localFileName = basename($localFile);
            if ($count == 0) {
                ConsoleCommand::log('vlabSyncCommand', 'DEBUG', "No hashes to process from file: {$originalFileName}");
                continue;
            }
            ConsoleCommand::log('vlabSyncCommand', 'DEBUG', "Processing {$count} hashes from file: {$originalFileName}");

            //  process the loaded content from the MySQL tables
            //  insert the data into Cassandra
            $response = $vlab->processLoadedTable($table, $localFile, $processID);

            if (isset($response['error']) && $response['error'] != '') {
                ConsoleCommand::log('vlabSyncCommand', 'DEBUG', "Error processing file: {$originalFileName}");
                ConsoleCommand::log('vlabSyncCommand', 'DEBUG', print_r($response['error'], true));
                continue;
            }

            $updated = isset($response['updated']) ? $response['updated'] : 0;
            $lessTrustLevel = isset($response['lessTrustLevel']) ? $response['lessTrustLevel'] : 0;

            if ($lessTrustLevel > 0 && isset($response['lessTrusted']) && is_array($response['lessTrusted']) && count($response['lessTrusted']) > 0)
                ConsoleCommand::log('vlabSyncCommand', 'DEBUG', "Hashes with less trustlevel from file: {$originalFileName}, that were not updated: " . print_r($response['lessTrusted'], true));

            if ($count != ($updated + $lessTrustLevel)) {
                ConsoleCommand::log('vlabSyncCommand', 'DEBUG', "Not all hashes processed from file: {$originalFileName}");
                continue;
            }

            //  move the file to the final folder
            //  it means that the file was processed successfully
            $moved = $vlab->moveFile(VLAB_REMOTE_HASHES_PROCESSING . "/{$localFileName}", VLAB_REMOTE_HASHES_FINAL . "/{$originalFileName}");

            DBHelper::dropTempTable($table);
            ConsoleCommand::log('vlabSyncCommand', 'DEBUG', 'Droped temporary table: ' . $table);

            ConsoleCommand::log('vlabSyncCommand', 'DEBUG', "{$count} hashes processed from file: {$originalFileName}");

            $numFiles++;
        }
    }

}