<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

<?php include "imports/head_parameters.php"; ?>

<?php

include_once "../classes/Feedback.php";
include_once "../controller/feedback_controller.php";

if(isset($_POST['descricao'])){
    $obj_feedback = new Feedback();

    $obj_feedback->id_usuario = $_SESSION['id_usuario']; 
    $obj_feedback->titulo = $_POST['titulo'];
    $obj_feedback->descricao = $_POST['descricao'];
    if(isset($_POST['anonimo'])){
        $obj_feedback->anonimo = 1;
    }else{
        $obj_feedback->anonimo = 0;
    }
    

    $feedback_controller = new FeedbackController();
    print_r($feedback_controller->salvar($obj_feedback));
}




?>
<title>Deixe seu feedback!</title>
<link rel="stylesheet" href="../view/css/form.css">
</head>

<body>
    <section class="body main">
        <?php include "imports/menu_lateral.php"; ?>
        <section class="main conteudo">
            <h1>Feedback</h1>
            <h2>Diga-nos o que achou!</h2>
            <form method="POST" action="feedback.php">
                <label class="form-check-label" for="ftitulo">Titulo</label>
                <input class="form-control" id="ftitulo" type="text" name="titulo" placeholder="Titulo" maxlength="20"></Input>
                <label class="form-check-label" for="fdescricao" title="Diga o que você acha sobre nós">Descrição</label>
                <textarea class="form-control" id="fdescricao" name="descricao" cols="60" rows="5" placeholder="Deixe sua dica: Melhoria, ajuste, função, elogio..." maxlength="500" title="Diga o que você acha sobre nós" required></textarea>
                <label class="form-check-label" for="fanonimo">Anônimo</label>
                <p class="disabled">Enviar o comentario anonimamente?</p>
                <input class="checkbox" id="fanonimo" type="checkbox" name="anonimo" maxlength="20">


                <button name="submit" class="bnt btn-success">Enviar</button>

            </form>
        </section>
    </section>
    <?php include "imports/js.php";?>
</body>

</html>