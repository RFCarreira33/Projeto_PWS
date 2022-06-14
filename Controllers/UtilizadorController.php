<?php 
require_once("./models/Auth.php");
require_once("./controllers/BaseController.php");

Class UtilizadorController extends Base
{
    public function index()
    {
        $auth = new Auth();

        if($auth->isLoggedIn())
        {  
            
            $user = Utilizador::find_by_username($_SESSION["username"]);
            
            $type = $user->role;
            
            $this->renderView($type);
        }
        else
        {
            $this->renderView("login");
        }
    }

    public function gestao()
    {
        $auth = new Auth();
        $role = $auth->getRole();
        if($role != "administrador"){ $this->redirectToRoute("");}

        $funcionarios = Utilizador::all(array('conditions' => 'role = "funcionario"'));
        $this->renderView("gestaofuncionario", ['funcionarios' => $funcionarios]);
    }

    public function show($type)
    {
        $auth = new Auth();
        $role = $auth->getRole();
        if($type == "cliente" && $role == "administrador" || $role == "funcionario")
        {
            $this->renderView("registerUser", ['type' => "Cliente"]);
        }
        else if($type == "funcionario" && $role == "administrador")
        {
            $this->renderView("registerUser", ['type' => "Funcionário"]);
        }
        else if($type == "update" && $role == "administrador")
        {
            $this->renderView("updateuser");
        }
        else
        {
            $this->redirectToRoute("");
        }
    }

    public function create($type)
    {
        $user = new Utilizador();
        $auth = new Auth();
        $role = $auth->getRole();

        if($type == "Funcionário" && $role == "administrador")
        {         
            $type = "funcionario";        
        }
        else if($role == "administrador" || $role == "funcionario")
        {
            $type = "cliente";
        }

        $dados = 
        [
            "username" => $_POST["user"],
            "pass" => hash("sha256", $_POST["pass"]),
            "email" => $_POST["email"],
            "telefone" => $_POST["tele"],
            "nif" => $_POST["nif"],
            "morada" => $_POST["morada"],
            "localidade" => $_POST["local"],
            "codigopostal" => $_POST["cod"],
            "role" => $type
        ];

        if($user->searchUsername($_POST["user"])){

       
        if($user->verificarDados($dados))
        {
            $user::create($dados);

            if($type == "funcionario")
            {
                $this->redirectToRoute("user/gestao");
            }
            else
            {
                $this->redirectToRoute("");
            }
        }
        else
        {  
            $this->renderView("erro", ["error" => "Erro nos parametros fornecidos", "route" => "user/show", "type" => $type]); 
        }
    }
    else
        {  
            $this->renderView("erro", ["error" => "Erro Username em uso", "route" => "user/show", "type" => $type]); 
        }
}



    public function edit($id)
    {
        $auth = new Auth();
        $role = $auth->getRole();

        if($role == "administrador"){

        $dados = 
        [
            "username" => $_POST["user"],
            "pass" => hash("sha256", $_POST["pass"]),
            "email" => $_POST["email"],
            "telefone" => $_POST["tele"],
            "nif" => $_POST["nif"],
            "morada" => $_POST["morada"],
            "localidade" => $_POST["local"],
            "codigopostal" => $_POST["cod"]

        ];

        $user = Utilizador::find_by_id($id);

        if($user->verificarDados($dados))
        {
            extract($dados);
            $user->update_attributes(array("username" => $username, "pass" => $pass, "email" => $email,
             "telefone" => $telefone, "nif" => $nif, "morada" => $morada, "localidade" => $localidade, "codigopostal" => $codigopostal));

            $this->redirectToRoute("user/gestao");
        }
        else
        {
            $this->renderView("error", ["erro" => "Erro nos parametros fornecidos", "route" => "user/show"]);  
        }
    }else{
        $this->redirectToRoute("");
    }
    
    }

    public function update($id)
    {
        $auth = new Auth();
        $role = $auth->getRole();
        if($role == "administrador"){
            $user = Utilizador::find_by_id($id);
            $this->renderView("updatefuncionario", ['user' => $user]);
        }
        else{
            $this->redirectToRoute("");
        }

        
    }

    public function change(){
        $auth = new Auth();

        if($auth->isLoggedIn())
        {
            $user = Utilizador::find_by_username($_SESSION["username"]);

            if(isset($_POST["pass"]))
            {
                $user->pass = hash("sha256", $_POST["pass"]);
            }
    
            if(isset($_POST["email"]))
            {
                $user->email = $_POST["email"];
            }

            $user->save();

            $this->redirectToRoute("auth/logout");
        }
        else
        {
            $this->redirectToRoute("");
        }
    }

    public function delete($id)
    {
        $auth = new Auth();
        $role = $auth->getRole();
        if($role == "administrador"){
            $utilizador = Utilizador::find_by_id($id);
            if($utilizador->role == "administrador"){
                $this->redirectToRoute("");
            }else{

            if($utilizador->isUsed($id)){
                $utilizador->delete();
                $this->redirectToRoute("user/gestao");
            }
        }
    
            $this->renderView("erro", ["error" => "Erro Utilizador não pode ser eliminado pois foi de uma fatura", "route" => "user/gestao", "type" => ""]);
        }
        $this->redirectToRoute("");

    }
}