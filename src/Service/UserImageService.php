<?php

namespace MartenaSoft\UserBundle\Service;

use MartenaSoft\CommonLibrary\Dictionary\ImageDictionary;
use MartenaSoft\SdkBundle\Service\ImageConfigServiceSdk;
//use MartenaSoft\SdkBundle\Service\ImageServiceSdk;
use MartenaSoft\CommonLibrary\Dto\ActiveSiteDto;

readonly class UserImageService
{
    public function __construct(
      //  private ImageServiceSdk $imageServiceSdk,
        private ImageConfigServiceSdk $imageConfigServiceSdk,
    )
    {

    }

    public function get(array $uuid, ActiveSiteDto $activeSiteDto): array
    {
//        return $this->imageServiceSdk->getImages(
//            type: ImageDictionary::TYPE_USER,
//            uuid: $uuid,
//            activeSiteDto: $activeSiteDto,
//        );
    }

    public function getImageConfig(ActiveSiteDto $activeSiteDto): array
    {
        return $this->imageConfigServiceSdk->get($activeSiteDto, ImageDictionary::TYPE_USER);
    }
}
