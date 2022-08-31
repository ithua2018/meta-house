<?php

namespace App\Http\Requests\Mobile;

use App\Rules\ImageData;

abstract class AbstractMediaImageUpdateRequest extends Request
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<mixed> */
    public function rules(): array
    {
        return [
            $this->getImageFieldName() => ['string', 'required', new ImageData()],
        ];
    }

    public function getFileContentAsBinaryString(): string
    {
        [, $data] = explode(',', $this->{$this->getImageFieldName()});

        return base64_decode($data, true);
    }

    public function getFileExtension(): string
    {
        [$type,] = explode(';', $this->{$this->getImageFieldName()});
        [, $extension] = explode('/', $type);

        return $extension;
    }

    abstract protected function getImageFieldName(): string;
}
