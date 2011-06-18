Esta library funciona bem com a versão *2.0.2* do CodeIgniter. Serve para criar
formulários de forma rápida e facilita a seleção de dados no banco de dados.

Instanciando a biblioteca
=========================

No seu controller, você deve fazer a chamada do seu banco de dados e, em seguida,
a chamada da library.

```
$this->load->database();
$this->load->library('modeler/modeler');
```

Ou, coloque no seu aquivo autoload.php para carregá-lo junto com a biblioteca de
banco de dados.

```
$autoload['libraries'] = array('database', 'modeler/modeler');
```

Usando a biblioteca
===================

Primeiro, você precisa criar um model para manipular o banco de dados. A biblioteca
possui uma biblioteca para facilitar todas as manipulações do banco de dados, chamada
*Modeler_ARecord*.
