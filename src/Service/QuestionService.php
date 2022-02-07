<?php

namespace App\Service;

use App\Entity\Question;

class QuestionService
{
    public function checkAnswer(Question $question, string $answer): bool
    {
        if($this->compare($question->getAnswer(), $answer)){
            return true;
        }
        $otherAnswers = explode(",", $question->getOtherAnswer());
        foreach ($otherAnswers as $otherAnswer){
            if($this->compare($otherAnswer, $answer)){
                return true;
            }
        }
        return false;
    }

    public function compare(string $str1, string $str2): bool
    {
        return $this->transformerEnURL($str1) === $this->transformerEnURL($str2);
    }

    public function transformerEnURL($string)
    {
        $string = $this->enleverCaracteresSpeciaux($string);
        $string = strtolower(str_replace(' ', '', $string));
        return preg_replace('[^A-Za-z0-9-]', '', $string);
    }

    public function enleverCaracteresSpeciaux($text)
    {
        $utf8 = array(
            '/[áàâãªä]/u'   =>   'a',
            '/[ÁÀÂÃÄ]/u'    =>   'A',
            '/[ÍÌÎÏ]/u'     =>   'I',
            '/[íìîï]/u'     =>   'i',
            '/[éèêë]/u'     =>   'e',
            '/[ÉÈÊË]/u'     =>   'E',
            '/[óòôõºö]/u'   =>   'o',
            '/[ÓÒÔÕÖ]/u'    =>   'O',
            '/[úùûü]/u'     =>   'u',
            '/[ÚÙÛÜ]/u'     =>   'U',
            '/ç/'           =>   'c',
            '/Ç/'           =>   'C',
            '/ñ/'           =>   'n',
            '/Ñ/'           =>   'N',
            '/–/'           =>   '-', // UTF-8 hyphen to "normal" hyphen
            '/[’‘‹›‚]/u'    =>   ' ', // Literally a single quote
            '/[“”«»„]/u'    =>   ' ', // Double quote
            '/ /'           =>   ' ', // nonbreaking space (equiv. to 0x160)
        );
        return preg_replace(array_keys($utf8), array_values($utf8), $text);
    }
}