<?php
        include "Bartercoin.php";
        $merchant_id = 5;
        $password1 = "23250q";
        $password2 = "23250w";
        $bc = new Bartercoin($merchant_id, $password1, $password2);
        $result = [];
        try {
                $result['success'] = $bc->checkPaymentOrderSign($_POST);

                if ($result['success'] === true) {
                        // тут делать проверки на данные заказа
                        // например проверить сумму заказа по его номеру в базе

                        // если какие-то проверки не прошли, то нужно переменить статус проверики следующим образом
                        // $result['success'] = false;
                }
        } catch (Exception $e) {
                // Тут исключение, которое может возникнуть при проверке, в нашем случае некорректные настройки магазина
                die ('Повторите платеж позже');
        }

        echo json_encode($result);
?>