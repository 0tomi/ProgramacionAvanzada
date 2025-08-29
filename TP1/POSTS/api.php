<?php
// POSTS/api.php
session_start();
header('Content-Type: application/json; charset=utf-8');

$file = __DIR__."/data.json";
if(!file_exists($file)) file_put_contents($file,"[]");

$data = json_decode(file_get_contents($file),true) ?: [];
if(!isset($_SESSION["likes"])) $_SESSION["likes"]=[];

$action = $_GET["action"] ?? "list";

if($action==="list"){
  foreach($data as &$p){
    $p["viewer"] = ["liked"=>in_array($p["id"],$_SESSION["likes"])];
  }
  echo json_encode(["ok"=>true,"items"=>$data]);
  exit;
}

$input=json_decode(file_get_contents("php://input"),true);

if($action==="like"){
  $id=$input["post_id"]??"";
  foreach($data as &$p){
    if($p["id"]===$id){
      if(in_array($id,$_SESSION["likes"])){
        $p["counts"]["likes"]--;
        $_SESSION["likes"]=array_diff($_SESSION["likes"],[$id]);
        $liked=false;
      }else{
        $p["counts"]["likes"]++;
        $_SESSION["likes"][]=$id;
        $liked=true;
      }
      file_put_contents($file,json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
      echo json_encode(["ok"=>true,"liked"=>$liked,"like_count"=>$p["counts"]["likes"]]);
      exit;
    }
  }
}

if($action==="comment"){
  $id = $input["post_id"] ?? "";
  $text = trim($input["text"] ?? "");
  $author = trim($input["author"] ?? "") ?: "Anónimo";
  $parent = $input["parent_comment_id"] ?? null; // <-- nuevo

  if($text===""){ echo json_encode(["ok"=>false,"error"=>"Texto vacío"]); exit; }

  foreach($data as &$p){
    if($p["id"]===$id){
      if(!isset($p["replies"]) || !is_array($p["replies"])) $p["replies"] = [];

      // Generar ID simple (timestamp + rand) para el comentario
      $cid = (string)(time()).substr((string)mt_rand(1000,9999), -4);

      $comment = [
        "id" => $cid,
        "parent_id" => $parent ? (string)$parent : null,
        "author" => $author,
        "text" => $text,
        "created_at" => gmdate('c')
      ];

      // Agregar al arreglo plano
      array_unshift($p["replies"], $comment);

      // Incrementar contador total
      if(!isset($p["counts"]["replies"])) $p["counts"]["replies"] = 0;
      $p["counts"]["replies"]++;

      file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
      echo json_encode(["ok"=>true,"comment"=>$comment]);
      exit;
    }
  }
  echo json_encode(["ok"=>false,"error"=>"Post no encontrado"]);
  exit;
}

echo json_encode(["ok"=>false,"error"=>"Acción inválida"]);
