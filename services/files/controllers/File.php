<?php
namespace services\files\controllers;

use modules\emer\exceptions\EConnectionError;
use modules\crypto\Crypto;
use internals\lib\Output;
use Base32\Base32;

use services\files\lib\Files;

use modules\ness\lib\ness;
use modules\ness\Privateness;
use modules\ness\Creator;

class File {

    public function __construct()
    {
        try {

        } catch (\Throwable $e) {
            Output::error($e->getMessage());
            die();
        }
    }

    public function man()
    {
        Output::text('Manual');
    }

    public function quota()
    {
        try {
            if (empty($_POST['username'])) {
                Output::error('Param username not found');
                return false;
            }

            $shadowname = $_POST['username'];

            $pr = Creator::Privateness();
            $user = $pr->findShadow($shadowname);

            if (false === $user) {
                Output::error('User "' . $shadowname . '" not found');
                return false;
            }

            $res = $pr->verifyUser2way($_POST['data'], $_POST['sig'], $user);

            if (true === $res) {
                $data = json_encode(Files::quota($user->getUsername()));
                $sig = '';
    
                $pr->encryptUser2way($data, $sig, $user);
                Output::encrypted($data, $sig);
            } else {
                Output::error('Signature check FAILED');
            }
        } catch (\Throwable $e) {
            Output::error($e->getMessage());
            return false;
        }
    }

    public function list()
    {
        try {
            if (empty($_POST['username'])) {
                Output::error('Param username not found');
                return false;
            }

            $shadowname = $_POST['username'];

            $pr = Creator::Privateness();
            $user = $pr->findShadow($shadowname);

            if (false === $user) {
                Output::error('User "' . $shadowname . '" not found');
                return false;
            }

            $res = $pr->verifyUser2way($_POST['data'], $_POST['sig'], $user);

            if (true === $res) {
                $data = json_encode(['files' => Files::listFiles( $user->getUsername())]);
                $sig = '';
    
                $pr->encryptUser2way($data, $sig, $user);
                Output::encrypted($data, $sig);
            } else {
                Output::error('Signature check FAILED');
            }
        } catch (\Throwable $e) {
            Output::error($e->getMessage());
            return false;
        }
    }

    public function fileinfo()
    {
        try {
            if (empty($_POST['username'])) {
                Output::error('Param username not found');
                return false;
            }

            if (empty($_POST['file_id'])) {
                Output::error('Param file_id not found');
                return false;
            }

            $shadowname = $_POST['username'];
            $file_id = $_POST['file_id'];

            $pr = Creator::Privateness();
            $user = $pr->findShadow($shadowname);

            if (false === $user) {
                Output::error('User "' . $shadowname . '" not found');
                return false;
            }

            $res = $pr->verifyUser2way($_POST['data'], $_POST['sig'], $user);

            if (true === $res) {
                $filename = Files::findFile($user->getUsername(), $file_id);

                if (false === $filename) {
                    Output::error("File $filename not found");
                    return false;
                }

                $fileinfo = Files::fileinfo($user->getUsername(), $filename);

                if (false === $fileinfo) {
                    Output::error("Can not extract fileinfo for $filename");
                    return false;
                }

                $data = json_encode($fileinfo);
                $sig = '';
    
                $pr->encryptUser2way($data, $sig, $user);
                Output::encrypted($data, $sig);
            } else {
                Output::error('Signature check FAILED');
            }
        } catch (\Throwable $e) {
            Output::error($e->getMessage());
            return false;
        }
    }

    public function download(string $file_id, string $username, string $id)
    {
        try {
            $pr = Creator::Privateness();
            $user = $pr->findShadow($username);

            if (false === $user) {
                Output::error('User "' . $username . '" not found');
                return false;
            }

            $res = $pr->verifyUserId($id, $user);

            if (true === $res) {
                $filename = Files::findFile($user->getUsername(), $file_id);

                if (false === $filename) {
                    Output::error("File $filename not found");
                    // header("HTTP/1.1 404 Not Found");
                    return false;
                }

                $fullname = Files::checkUserPath($user->getUsername()) . '/' . $filename;

                // $mime = mime_content_type($fullname);
                $contentType = 'application/octet-stream';

                try {
                    // Note that this construct will still work if the client did not specify a Range: header
                    $rangeHeader = \DaveRandom\Resume\get_request_header('Range');
                    $rangeSet = \DaveRandom\Resume\RangeSet::createFromHeader($rangeHeader);
                
                    /** @var \DaveRandom\Resume\Resource $resource */
                    $resource = new \DaveRandom\Resume\FileResource($fullname , $contentType);
                    $servlet = new \DaveRandom\Resume\ResourceServlet($resource);
                
                    $servlet->sendResource($rangeSet);
                } catch (\DaveRandom\Resume\InvalidRangeHeaderException $e) {
                    header("HTTP/1.1 400 Bad Request");
                } catch (\DaveRandom\Resume\UnsatisfiableRangeException $e) {
                    header("HTTP/1.1 416 Range Not Satisfiable");
                } catch (\DaveRandom\Resume\UnreadableFileException $e) {
                    header("HTTP/1.1 500 Internal Server Error");
                } catch (\DaveRandom\Resume\SendFileFailureException $e) {
                    if (!headers_sent()) {
                        header("HTTP/1.1 500 Internal Server Error");
                    }
                
                    echo "An error occurred while attempting to send the requested resource: {$e->getMessage()}";
                }
            } else {
                Output::error('User auth ID FAILED');
            }
        } catch (\Throwable $e) {
            Output::error($e->getMessage());
            return false;
        }
    }

    public function touch()
    {
        try {
            if (empty($_POST['username'])) {
                Output::error('Param username not found');
                return false;
            }

            if (empty($_POST['filename'])) {
                Output::error('Param filename not found');
                return false;
            }

            $shadowname = $_POST['username'];
            $filename = $_POST['filename'];

            $pr = Creator::Privateness();
            $user = $pr->findShadow($shadowname);

            if (false === $user) {
                Output::error('User "' . $shadowname . '" not found');
                return false;
            }

            if (!$pr->IsActiveOrMaster($user->getUsername())) {
                Output::error('User "' . $user->getUsername() . '" is Inactive');
                return false;
            }

            if (Files::quota($user->getUsername())['quota']['free'] <= 0) {
                Output::error('All disk space quota used');
                return false;
            }

            $res = $pr->verifyUser2way($_POST['data'], $_POST['sig'], $user);

            if (true === $res) {
                $filename = $pr->decryptUser2way($filename);
                $oldfilename = $filename;
                $filename = Files::filename($filename);

                if (false === $filename) {
                    Output::error("Invalid filename '$oldfilename'");
                    return false;
                }

                $fullname = Files::checkUserPath($user->getUsername()) . '/' . $filename;

                if (file_exists($fullname)) {
                    $data = json_encode(['size' => filesize($fullname), 'id' => Files::fileID($filename)]);
                    $sig = '';
        
                    $pr->encryptUser2way($data, $sig, $user);
                    Output::encrypted($data, $sig);

                    return True;
                }

                $file = fopen($fullname, 'w'); 
                fclose($file);
                $data = json_encode(['size' => 0, 'id' => Files::fileID($filename)]);
                $sig = '';
    
                $pr->encryptUser2way($data, $sig, $user);
                Output::encrypted($data, $sig);
            } else {
                Output::error('User auth ID FAILED');
            }
        } catch (\Throwable $e) {
            Output::error($e->getMessage());
            return false;
        }
    }

    public function rewrite()
    {
        try {
            if (empty($_POST['username'])) {
                Output::error('Param username not found');
                return false;
            }

            if (empty($_POST['filename'])) {
                Output::error('Param filename not found');
                return false;
            }

            $shadowname = $_POST['username'];
            $filename = $_POST['filename'];

            $pr = Creator::Privateness();
            $user = $pr->findShadow($shadowname);

            if (false === $user) {
                Output::error('User "' . $shadowname . '" not found');
                return false;
            }

            if (!$pr->IsActiveOrMaster($user->getUsername())) {
                Output::error('User "' . $user->getUsername() . '" is Inactive');
                return false;
            }

            if (Files::quota($user->getUsername())['quota']['free'] <= 0) {
                Output::error('All disk space quota used');
                return false;
            }

            $res = $pr->verifyUser2way($_POST['data'], $_POST['sig'], $user);

            if (true === $res) {
                $filename = $pr->decryptUser2way($filename);
                $oldfilename = $filename;
                $filename = Files::filename($filename);

                if (false === $filename) {
                    Output::error("Invalid filename '$oldfilename'");
                    return false;
                }

                $fullname = Files::checkUserPath($user->getUsername()) . '/' . $filename;

                if (file_exists($fullname)) {
                    unlink($fullname);
                }

                $file = fopen($fullname, 'w'); 
                fclose($file);
                $data = json_encode(['size' => 0, 'id' => Files::fileID($filename)]);
                $sig = '';
    
                $pr->encryptUser2way($data, $sig, $user);
                Output::encrypted($data, $sig);
            } else {
                Output::error('User auth ID FAILED');
            }
        } catch (\Throwable $e) {
            Output::error($e->getMessage());
            return false;
        }
    }

    public function remove()
    {
        try {
            if (empty($_POST['username'])) {
                Output::error('Param username not found');
                return false;
            }

            if (empty($_POST['file_id'])) {
                Output::error('Param file_id not found');
                return false;
            }

            $shadowname = $_POST['username'];
            $file_id = $_POST['file_id'];

            $pr = Creator::Privateness();
            $user = $pr->findShadow($shadowname);

            if (false === $user) {
                Output::error('User "' . $shadowname . '" not found');
                return false;
            }

            if (!$pr->IsActiveOrMaster($user->getUsername())) {
                Output::error('User "' . $user->getUsername() . '" is Inactive');
                return false;
            }

            $res = $pr->verifyUser2way($_POST['data'], $_POST['sig'], $user);

            if (true === $res) {
                $filename = Files::findFile($user->getUsername(), $file_id);
                $fullname = Files::checkUserPath($user->getUsername()) . '/' . $filename;

                $res = unlink($fullname);

                if (false === $res) {
                    Output::error('Param file_id not found ' . $file_id );
                    return false;
                }

                $data = 'OK';
                $sig = '';
    
                $pr->encryptUser2way($data, $sig, $user);
                Output::encrypted($data, $sig);
            } else {
                Output::error('User auth ID FAILED');
            }
        } catch (\Throwable $e) {
            Output::error($e->getMessage());
            return false;
        }
    }

    public function append(string $file_id, string $username,  string $id)
    {
        try {
            $pr = Creator::Privateness();
            $user = $pr->findShadow($username);

            if (false === $user) {
                Output::error('User "' . $username . '" not found');
                return false;
            }

            if (!$pr->IsActiveOrMaster($user->getUsername())) {
                Output::error('User "' . $user->getUsername() . '" is Inactive');
                return false;
            }

            if (Files::quota($user->getUsername())['quota']['free'] <= 0) {
                Output::error('All disk space quota used');
                return false;
            }

            $res = $pr->verifyUserId($id, $user);

            if (true === $res) {
                $filename = Files::findFile($user->getUsername(), $file_id);

                if (false === $filename) {
                    header("HTTP/1.1 404 Not Found");
                    Output::error("File $filename not found");
                    return false;
                }

                $fullname = Files::checkUserPath($user->getUsername()) . '/' . $filename;

                $post_body = file_get_contents('php://input');

                file_put_contents($fullname, $post_body, FILE_APPEND);

                Output::data(['size' => filesize($fullname)]);
            } else {
                Output::error('User auth ID FAILED');
            }
        } catch (\Throwable $e) {
            Output::error($e->getMessage());
            return false;
        }
    }

    public function pub(string $file_id, string $username, string $id)
    {
        try {
            $pr = Creator::Privateness();
            $user = $pr->findShadow($username);

            if (false === $user) {
                Output::error('User "' . $username . '" not found');
                return false;
            }

            if (!$pr->IsActiveOrMaster($user->getUsername())) {
                Output::error('User "' . $username . '" is Inactive');
                return false;
            }

            $res = $pr->verifyAlternativeUserId($id, $user);

            if (true === $res) {
                $filename = Files::findFile($user->getUsername(), $file_id);

                if (false === $filename) {
                    Output::error("File $filename not found");
                    // header("HTTP/1.1 404 Not Found");
                    return false;
                }

                $fullname = Files::checkUserPath($user->getUsername()) . '/' . $filename;

                $contentType = 'application/octet-stream';

                try {
                    // Note that this construct will still work if the client did not specify a Range: header
                    $rangeHeader = \DaveRandom\Resume\get_request_header('Range');
                    $rangeSet = \DaveRandom\Resume\RangeSet::createFromHeader($rangeHeader);
                
                    /** @var \DaveRandom\Resume\Resource $resource */
                    $resource = new \DaveRandom\Resume\FileResource($fullname , $contentType);
                    $servlet = new \DaveRandom\Resume\ResourceServlet($resource);
                
                    $servlet->sendResource($rangeSet);
                } catch (\DaveRandom\Resume\InvalidRangeHeaderException $e) {
                    header("HTTP/1.1 400 Bad Request");
                } catch (\DaveRandom\Resume\UnsatisfiableRangeException $e) {
                    header("HTTP/1.1 416 Range Not Satisfiable");
                } catch (\DaveRandom\Resume\UnreadableFileException $e) {
                    header("HTTP/1.1 500 Internal Server Error");
                } catch (\DaveRandom\Resume\SendFileFailureException $e) {
                    if (!headers_sent()) {
                        header("HTTP/1.1 500 Internal Server Error");
                    }
                
                    echo "An error occurred while attempting to send the requested resource: {$e->getMessage()}";
                }
            } else {
                Output::error('User auth ID FAILED');
            }
        } catch (\Throwable $e) {
            Output::error($e->getMessage());
            return false;
        }
    }
}
