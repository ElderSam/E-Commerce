# Projeto E-Commerce Administração e Site (PHP)

Projeto desenvolvido do zero no [Curso de PHP 7](https://www.udemy.com/curso-completo-de-php-7/) disponível na plataforma da Udemy e no site do [HTML5dev.com.br](https://www.html5dev.com.br/curso/curso-completo-de-php-7).

Template usado no projeto [Almsaeed Studio](https://almsaeedstudio.com)

Para funcionar funções de mandar e-mail como a recuperação de senha, você precisa colocar um e-mail padrão do sistema para ele ser o Remetente:

na Classe Mailer no caminho: C:\E-Commerce\vendor\eldersam\php-classes\src\Mailer.php

coloque seu e-mail (Gmail) e senha nas Constantes abaixo (MAS LEMBRE-SE DE APAGAR SE VOCÊ FOR SUBIR O PROJETO PARA O GITHUB, PARA NINGUÉM ROUBAR SUA CONTA): 

    const USERNAME = "youremail.com";  //here your email
    const PASSWORD = "*********"; //here your password
    const NAME_FROM = "youremail.com";

O próximo passo é permitir que a Conta Google aceite apps menos seguros:
Entre no link:
https://support.google.com/accounts/answer/6010255?hl=en

Clique na opção: If "Less secure app access" is off for your account"
e em baixo vai aparecer o link: "turn it back on"
Depois é só ativar

OBS: Não esqueça de desativar essa opção depois, por sua segurança.