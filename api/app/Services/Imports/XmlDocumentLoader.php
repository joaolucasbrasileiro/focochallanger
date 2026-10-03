<?php

namespace App\Services\Imports;

use App\Exceptions\Imports\XmlImportException;
use SimpleXMLElement;

class XmlDocumentLoader
{
    public function load(string $path): SimpleXMLElement
    {
        if (! is_file($path) || ! is_readable($path)) {
            throw new XmlImportException("Não foi possível ler o arquivo XML [{$path}].");
        }

        $previousState = libxml_use_internal_errors(true);

        try {
            // SimpleXMLElement::class informa que queremos receber um objeto XML SimpleXMElement do $path
            // LIBXML_NONET é uma medida de segurança, para que durante o processo
            // n seja possível acessar a internet (http, ftp).
            $document = simplexml_load_file($path, SimpleXMLElement::class, LIBXML_NONET);

            // caso simplexml_load_file n tenha conseguido ler o xml, $document será false
            // logo terá algum libxml erro ou não, resultado de qualquer forma no lançamento da exception q será propagada
            // posteriomente tratada no XmlImportService
            if ($document === false) {
                $error = libxml_get_errors()[0] ?? null;
                $technicalMessage = $error === null ? null : trim($error->message);

                throw new XmlImportException(
                    "Não foi possível interpretar o arquivo XML [{$path}]. O conteúdo XML é inválido.",
                    $technicalMessage,
                );
            }

            // retorna o objeto xml
            return $document;

            // reseta e limpa o buffer interno com os xml armazenado
            // desse modo evita-se que erros de um import possam ir para outro
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousState);
        }
    }
}
