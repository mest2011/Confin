<?php

// if (isset($_SERVER['HTTP_ORIGIN'])) {
//     header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
//     header('Access-Control-Allow-Credentials: true');
//     header('Access-Control-Max-Age: 86400');    // cache for 1 day
// }

// // Access-Control headers are received during OPTIONS requests
// if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

//     if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
//         header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

//     if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
//         header("Access-Control-Allow-Headers:        {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
// }

include_once "../business/usuario_bus.php";
include_once "../classes/Usuario.php";
include_once "../controller/session_controller.php";



if (!isset($_SESSION)) {
    session_start();
}


if (isset($_POST['funcao'])) {

    //valida sessão
    $sessao = new Session_security("isValid");

    if (!$sessao->validaSessao("isValid")) {
        print_r(json_encode("Erro : tempo de sessao excedido!"));
        exit();
    }

    if (!isset($_SESSION['id_usuario'])) {
        print_r(json_encode("Erro na sessao atual, entre novamente no sistema!"));
        exit();
    }

    $user_controller = new Usuarios($_SESSION['id_usuario']);

    //$user_controller = new Usuarios(1);

    try {
        //Savar foto pefil
        if ($_POST['funcao'] == 'salvarfoto' and isset($_POST['id'])) {

            if (isset($_POST['tipo'])) {
                //set default thumb
                if ($_POST['tipo'] == "default") {
                    $_POST['funcao'] = 'salvar';
                    $_POST['foto'] = "default.svg";
                    $_SESSION['foto'] = "default.svg";
                }
                //set new foto
                else {
                    $hash_name = hash('ripemd160', date("Y/m/d H:i:s"));

                    if (isset($_FILES["foto"]["name"], $_FILES["foto"]["type"], $_FILES["foto"]["size"])) {
                        $nomeArqFoto      = $_FILES["foto"]["name"];
                        $tipoArqFoto     = $_FILES["foto"]["type"];
                        $tamanhoArqFoto     = $_FILES["foto"]["size"];
                        $nomeTempArqFoto = $_FILES["foto"]["tmp_name"];

                        if ($tipoArqFoto != "image/jpg" && $tipoArqFoto != "image/jpeg") {
                            print_r(json_encode("Erro : Arquivo fora do formato aceito! Apenas JPG e JPEG!"));
                            exit();
                        }

                        try {
                            $imgConvert = new Simpleimage();
                            $imgConvert->load($nomeTempArqFoto);
                            $imgConvert->resizeToWidth(200);
                            $imgConvert->crop();
                            $imgConvert->save("../../uploads/{$_POST['id']}arq.{$hash_name}.jpg");

                            $_POST['funcao'] = 'salvar';
                            $_POST['foto'] = "{$_POST['id']}arq.{$hash_name}.jpg";
                            $_SESSION['foto'] = "{$_POST['id']}arq.{$hash_name}.jpg";

                            //print_r(json_encode("Foto salva com sucesso!"));

                        } catch (\Throwable $th) {
                            print_r(json_encode("Erro : Erro ao salvar o arquivo!"));
                            exit();
                        }
                    } else {
                        print_r(json_encode("Erro : Erro ao receber o arquivo!"));
                        exit();
                    }
                }
            }
        }
        //Create and Update
        if ($_POST['funcao'] == 'salvar') {

            $user = new Usuario();

            if (isset($_POST['foto'])) $user->foto = $_POST['foto'];
            if (isset($_POST['id'])) $user->id_usuario = $_POST['id'];
            if (isset($_POST['nome'])) $user->nome = $_POST['nome'];
            if (isset($_POST['cpf'])) $user->cpf = $_POST['cpf'];
            if (isset($_POST['email'])) $user->email = $_POST['email'];
            if (isset($_POST['endereco'])) $user->endereco = $_POST['endereco'];
            if (isset($_POST['cidade'])) $user->cidade = $_POST['cidade'];
            if (isset($_POST['pais'])) $user->pais = $_POST['pais'];
            if (isset($_POST['datanascimento'])) $user->datanascimento = $_POST['datanascimento'];
            if (isset($_POST['usuario'])) $user->usuario = $_POST['usuario'];
            if (isset($_POST['senha'])) $user->senha = $_POST['senha'];

            print_r(json_encode($user_controller->salvarUsuario($user)));
            exit();
        }
        //Read
        if ($_POST['funcao'] == 'listar') {
            print_r(json_encode($user_controller->buscarUsuarios()));
            exit();
        }
        //Delete
        if ($_POST['funcao'] == 'excluir' and isset($_POST['id'])) {
            print_r(json_encode($user_controller->deletarUsuario($_POST['id'])));
            exit();
        }
    } catch (\Throwable $th) {
        print_r(json_encode("Erro : Erro no processamento da solicitação!"));
        exit();
    }
}


class Usuarios
{
    private $_id_usuario;

    function __construct($id_usuario)
    {
        $this->_id_usuario = $id_usuario;
    }

    function salvarUsuario($obj_ganho)
    {
        return UsuarioBus::createUsuario($this->_id_usuario, $obj_ganho);
    }

    function buscarUsuarios($id_usuario = 0)
    {
        if ($id_usuario == 0) $id_usuario = $this->_id_usuario;
        return UsuarioBus::readUsuario($id_usuario);
    }

    function deletarUsuario($id_usuario)
    {
        return UsuarioBus::deleteUsuario($id_usuario);
    }
}


class SimpleImage
{

    var $image;
    var $image_type;
    var $dir_file;

    function load($filename)
    {
        $this->dir_file = $filename;
        $image_info = getimagesize($filename);
        $this->image_type = $image_info[2];
        if ($this->image_type == IMAGETYPE_JPEG) {

            $this->image = imagecreatefromjpeg($filename);
        } elseif ($this->image_type == IMAGETYPE_GIF) {

            $this->image = imagecreatefromgif($filename);
        } elseif ($this->image_type == IMAGETYPE_PNG) {

            $this->image = imagecreatefrompng($filename);
        }
    }
    function save($filename, $image_type = IMAGETYPE_JPEG, $compression = 75, $permissions = null)
    {

        if ($image_type == IMAGETYPE_JPEG) {
            imagejpeg($this->image, $filename, $compression);
        } elseif ($image_type == IMAGETYPE_GIF) {

            imagegif($this->image, $filename);
        } elseif ($image_type == IMAGETYPE_PNG) {

            imagepng($this->image, $filename);
        }
        if ($permissions != null) {

            chmod($filename, $permissions);
        }
    }
    function output($image_type = IMAGETYPE_JPEG)
    {

        if ($image_type == IMAGETYPE_JPEG) {
            imagejpeg($this->image);
        } elseif ($image_type == IMAGETYPE_GIF) {

            imagegif($this->image);
        } elseif ($image_type == IMAGETYPE_PNG) {

            imagepng($this->image);
        }
    }
    function getWidth()
    {

        return imagesx($this->image);
    }
    function crop()
    {
        $image = imagecreatefromjpeg($this->dir_file);
        $filename = $this->image;

        $thumb_width = 200;
        $thumb_height = 200;

        $width = imagesx($image);
        $height = imagesy($image);

        $original_aspect = $width / $height;
        $thumb_aspect = $thumb_width / $thumb_height;

        if ($original_aspect >= $thumb_aspect) {
            // If image is wider than thumbnail (in aspect ratio sense)
            $new_height = $thumb_height;
            $new_width = $width / ($height / $thumb_height);
        } else {
            // If the thumbnail is wider than the image
            $new_width = $thumb_width;
            $new_height = $height / ($width / $thumb_width);
        }

        $thumb = imagecreatetruecolor($thumb_width, $thumb_height);

        // Resize and crop
        imagecopyresampled(
            $thumb,
            $image,
            0 - ($new_width - $thumb_width) / 2, // Center the image horizontally
            0 - ($new_height - $thumb_height) / 2, // Center the image vertically
            0,
            0,
            $new_width,
            $new_height,
            $width,
            $height
        );
        $this->image = $thumb;
    }
    function getHeight()
    {

        return imagesy($this->image);
    }
    function resizeToHeight($height)
    {

        $ratio = $height / $this->getHeight();
        $width = $this->getWidth() * $ratio;
        $this->resize($width, $height);
    }

    function resizeToWidth($width)
    {
        $ratio = $width / $this->getWidth();
        $height = $this->getheight() * $ratio;
        $this->resize($width, $height);
    }

    function scale($scale)
    {
        $width = $this->getWidth() * $scale / 100;
        $height = $this->getheight() * $scale / 100;
        $this->resize($width, $height);
    }

    function resize($width, $height)
    {
        $new_image = imagecreatetruecolor($width, $height);
        imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
        $this->image = $new_image;
    }
}

print_r(json_encode("Não executou nada"));
