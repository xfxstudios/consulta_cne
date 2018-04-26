# Validación de Cédula con el CNE Venezuela #
#### La presente es una adaptación de una clase existente pero no mantenida para la obtención de los datos relacionados a un usuario registrado en el CNE Venezuela ####

___Si desea ser colaborador ó agregar adaptaciones para usar en otros frameworks, por favor, clone el repo y envie sus merge request para revisión___

Para su uso en Codeigniter, se debe copiar la librería en la carpeta libraries en applications.

```
    $this->load->library('my_cne');

    $data = array("V","123456789");
    $this->my_cne->getCNE($data);
```

En PHP puro sería algo así:

```
    include('cne.class.php');

    $da = new CNE();

    $data = array("V","123456789");
    $p = $da->getCNE($data);
    var_dump($p);
```

__Para Donaciones: [PayPal](https://paypal.me/carlos14624/15)__