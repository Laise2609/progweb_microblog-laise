<?php
require "conecta.php";

/* Usada em post-insere.php */
function inserirPost(mysqli $conexao, string $titulo, string $texto, string $resumo, string $imagem, int $idUsuarioLogado){
    $sql = "INSERT INTO posts(titulo, texto, resumo, imagem, usuario_id) VALUES('$titulo', '$texto', '$resumo', '$imagem', '$idUsuarioLogado')";
    
    mysqli_query($conexao, $sql) or die(mysqli_error($conexao));
} // fim inserirPost



/* Usada em posts.php */
function lerPosts(mysqli $conexao, int $idUsuarioLogado, string $tipoUsuarioLogado):array {
    //se o tipo de usuário for admin
    if ($tipoUsuarioLogado == 'admin') {
        //SQL traga todos os posts (de qualquer um)
        $sql = "SELECT posts.id, posts.titulo, posts.data, usuarios.nome AS autor FROM posts INNER JOIN usuarios 
        ON posts.usuario_id = usuarios.id ORDER BY data DESC";
    } else {
        //SQL traga os posts do editor
        $sql = "SELECT id, titulo, data FROM posts WHERE usuario_id = $idUsuarioLogado ORDER BY data DESC";
    }

    $resultado = mysqli_query($conexao,$sql) or die(mysqli_error($conexao));
    $posts = [];
    while($post = mysqli_fetch_assoc($resultado)){
        array_push($posts, $post);
    }
    return $posts;
} // fim lerPosts


/* Usada em post-atualiza.php */
function lerUmPost(mysqli $conexao, int $idPost, int $idUsuarioLogado, string $tipoUsuarioLogado):array { 
    if($tipoUsuarioLogado == 'admin'){
        //Se o usuário logado for um admin, então pode carregar os dados de qualquer post de qualquer usuário
        $sql = "SELECT titulo, texto, resumo, imagem, usuario_id FROM posts WHERE id = $idPost";
    } else {
        //Caso contrário, significa que é um usuário editor, portanto só poderá carregar os dados dos seus próprios posts
        $sql = "SELECT titulo, texto, resumo, imagem, usuario_id FROM posts WHERE id = $idPost AND usuario_id = $idUsuarioLogado";
    }
	$resultado = mysqli_query($conexao, $sql) or die(mysqli_error($conexao));
    return mysqli_fetch_assoc($resultado); 
} // fim lerUmPost



/* Usada em post-atualiza.php */
function atualizarPost(mysqli $conexao, int $idPost, int $idUsuarioLogado, string $tipoUsuarioLogado, string $titulo, string $texto, string $resumo, string $imagem){
    if ($tipoUsuarioLogado == 'admin') {
        $sql = "UPDATE posts SET titulo = '$titulo', texto = '$texto', resumo = '$resumo', imagem = '$imagem' WHERE id = $idPost";
    } else {
        $sql = "UPDATE posts SET titulo = '$titulo', texto = '$texto', resumo = '$resumo', imagem = '$imagem' WHERE id = $idPost AND usuario_id = $idUsuarioLogado";
    }

    mysqli_query($conexao, $sql) or die(mysqli_error($conexao));       
} // fim atualizarPost



/* Usada em post-exclui.php */
function excluirPost(mysqli $conexao, int $idPost, int $idUsuarioLogado, string $tipoUsuarioLogado){    
    if ($tipoUsuarioLogado == 'admin') {
        $sql = "DELETE FROM posts WHERE id = $idPost";
    } else {
        $sql = "DELETE FROM posts WHERE id = $idPost AND usuario_id = $idUsuarioLogado";
    }

	mysqli_query($conexao, $sql) or die(mysqli_error($conexao));
} // fim excluirPost



/* Funções utilitárias */

/* Usada em post-insere.php e post-atualiza.php */  
function upload($arquivo){
    //Definindo os tipos de imagem aceitos
    $tiposValidos = ["image/png", "image/jpeg", "image/gif", "image/svg+xml"];

    //Verificando se o arquivo enviado não é um dos aceitos
    if(!in_array($arquivo['type'], $tiposValidos) ){
        die("<script> alert('Este formato de imagem é inválido!');history.back();</script>");
    }

    //Acessando apenas o nome do arquivo
    $nome = $arquivo['type']; //$_FILES['arquivo']['name']

    //Acessando dados de acesso temporário ao arquivo
    $temporario = $arquivo['tmp_name'];

    //Pasta de destino do arquivo que está sendo enviado
    $destino = "../imagens/$nome";

    //Se o processo de envio temporario para destino for feito com sucesso, então a fnção retorna verdadeiro (indicando o sucesso do processo)
    if(move_uploaded_file($temporario, $destino) ){
        return true;
    }
    
} // fim upload



/* Usada em posts.php e páginas da área pública */
function formataData(string $data):string{ 
    //Pegamos uma data informada, transofrmamos em texto (strtotime) e depois aplicamos o formato brasileiro ("dia/mês/ANO")
    return  date("d/m/Y H:i", strtotime($data));
} // fim formataData



/***Funções para a área PÚBLICA do site ***/

/* Usada em index.php */
function lerTodosOsPosts(mysqli $conexao):array {
    $sql = "SELECT id, titulo, imagem, resumo FROM posts ORDER BY data DESC";
    
    $resultado = mysqli_query($conexao, $sql) or die(mysqli_error($conexao));
    $posts = [];
    while( $post = mysqli_fetch_assoc($resultado) ){
        array_push($posts, $post);
    }
    return $posts; 
} // fim lerTodosOsPosts



/* Usada em post-detalhe.php */
function lerDetalhes(mysqli $conexao, int $idPost):array {    
    $sql = "SELECT posts.id, posts.titulo, posts.imagem, posts.data, posts.texto, usuarios.nome AS autor 
    FROM posts INNER JOIN usuarios ON posts.usuario_id = usuarios.id WHERE posts.id = $idPost";

    $resultado = mysqli_query($conexao, $sql) or die(mysqli_error($conexao));
    return mysqli_fetch_assoc($resultado); 
} // fim lerDetalhes

 

/* Usada em search.php */
function busca(mysqli $conexao, string $termo):array {
    $sql = "SELECT id, titulo, data, resumo FROM posts WHERE titulo LIKE '%$termo%' OR resumo LIKE '%$termo%' OR texto LIKE '%$termo%' ORDER BY data DESC";
        
    $resultado = mysqli_query($conexao, $sql) or die(mysqli_error($conexao));
    $posts = [];
    while( $post = mysqli_fetch_assoc($resultado) ){
        array_push($posts, $post);
    }
    return $posts; 
} // fim busca