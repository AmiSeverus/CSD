<?php

define('DEBUG', true);

if (DEBUG)
{
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

class FormHandler {

    private $data;
    private $emptyField;
    private $n10 = [2,4,10,3,5,9,4,6,8];
    private $n12 = [[7,2,4,10,3,5,9,4,6,8],[3,7,2,4,10,3,5,9,4,6,8]];

    function __construct($post)
    {
        $this->data = $post;
        // print_r($this->data);
    }

    private function checkForm()
    {
        foreach($this->data as $key=>$field)
        {
            if (empty($field))
            {
                $this->emptyField = $key;
                return false;
            }
        }
        return true;
    }

    private function checkInnLength()
    {
        if (strlen($this->data['inn']) == 12)
        {
            return 12;
        };
        if (strlen($this->data['inn']) == 10)
        {
            return 10;
        };
        return false;
    }

    private function checkInnFormat()
    {
        return ((preg_match('/^[0-9]+$/', $this->data['inn'])));
    }

    private function validateInn10()
    {
        $k1 = 0;
        for ($i = 0; $i < count($this->n10); $i++)
        {
            $k1 += $this->n10[$i]*$this->data['inn'][$i];
        };
        $k1 = $k1 % 11;
        if ($k1 > 9)
        {
            $k1 = $k1 % 10;
        };
        return($k1 == $this->data['inn'][9]);
    }

    private function validateInn12()
    {
        $k1 = 0;
        for ($i = 0; $i < count($this->n12[0]); $i++)
        {
            $k1 += $this->n12[0][$i]*$this->data['inn'][$i];
        };
        $k1 = $k1 % 11;
        if ($k1 > 9)
        {
            $k1 = $k1 % 10;
        };
        if ($k1 != $this->data['inn'][10])
        {
            return false;
        }
        $k2 = 0;
        for ($i = 0; $i < count($this->n12[1]); $i++)
        {
            $k2 += $this->n12[1][$i]*$this->data['inn'][$i];
        };
        $k2 = $k2 % 11;
        if ($k2 > 9)
        {
            $k2 = $k2 % 10;
        };
        if ($k2 != $this->data['inn'][11])
        {
            return false;
        }
        return true;
    }

    public function processForm()
    {
        if (!$this->checkForm())
        {
            return 'Поле ' . $this->emptyField . ' незаполнено';
        }

        if ($this->checkInnFormat() < 1)
        {
            return 'ИНН должен содержать только цифры';
        }

        $len = $this->checkInnLength();
        if (!$len)
        {
            return 'Длина ИНН должна быть 10 или 12 символов';
        }

        $name = 'validateInn'.$len;
        if (!$this->$name())
        {
            return 'ИНН не прошел валидацию';
        }

        $file = fopen('csd.csv', 'a');

        foreach ($this->data as $field)
        {
            if (!fwrite($file, $field . ';'))
            {
                return 'ЧТо-то пошло не так с записью в файл';
            }
        };

        if (!fwrite($file, '\n'))
        {
            return 'ЧТо-то пошло не так с записью в файл';
        };

        fclose($file);

        return ('Форма проверку прошла, данные сохранены');
    }

}

$obj = new FormHandler($_POST);

print($obj->processForm());