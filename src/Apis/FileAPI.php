<?php

namespace Recca0120\Upload\Apis;

class FileAPI extends Base
{
    /**
     * getOriginalName.
     *
     * @return string
     */
    protected function getOriginalName()
    {
        $originalName = $this->request->get('name');
        if (empty($originalName) === true) {
            list($originalName) = sscanf(
                $this->request->header('content-disposition'),
                'attachment; filename=%s'
            );
        }

        return $originalName;
    }

    /**
     * getMimeType.
     *
     * @param string $originalName
     * @return string
     */
    protected function getMimeType($originalName)
    {
        $mimeType = $this->request->header('content-type');
        if (empty($mimeType) === true) {
            $mimeType = $this->filesystem->mimeType($originalName);
        }

        return $mimeType;
    }

    /**
     * receive.
     *
     * @param string $inputName
     * @return \Symfony\Component\HttpFoundation\File\UploadedFile
     *
     * @throws \Recca0120\Upload\Exceptions\ChunkedResponseException
     */
    protected function doReceive($inputName)
    {
        $contentRange = $this->request->header('content-range');
        if (empty($contentRange) === true) {
            return $this->request->file($inputName);
        }
        list($start, $end, $total) = sscanf($contentRange, 'bytes %d-%d/%d');

        return $this->receiveChunkedFile(
            $originalName = $this->getOriginalName(),
            'php://input',
            $start,
            $end >= $total - 1,
            ['mimeType' => $this->getMimeType($originalName), 'headers' => ['X-Last-Known-Byte' => $end]]
        );
    }
}
