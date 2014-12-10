<?php

namespace Hostel\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * File
 */
class File
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $path;

	function __construct()
	{
		$this->upload_date = new \DateTime();
	}

	/**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set path
     *
     * @param string $path
     * @return File
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string 
     */
    public function getPath()
    {
        return $this->path;
    }


	public function getAbsolutePath()
	{
		return null === $this->path
			? null
			: self::getUploadRootDir().'/'.$this->path;
	}

	public function getWebPath()
	{
		return null === $this->path
			? null
			: self::getUploadDir().'/'.$this->path;
	}

	public static function getUploadRootDir()
	{
		// the absolute directory path where uploaded
		// documents should be saved
		return __DIR__.'/../../../../web'.self::getUploadDir();
	}

	public static function getUploadDir()
	{
		// get rid of the __DIR__ so it doesn't screw up
		// when displaying uploaded doc/image in the view.
		return '/uploads';
	}

	/**
	 * @Assert\File(maxSize="2000000")
	 */
	private $file;

	/**
	 * Sets file.
	 *
	 * @param UploadedFile $file
	 */
	public function setFile(UploadedFile $file = null)
	{
		$this->file = $file;
	}

	/**
	 * Get file.
	 *
	 * @return UploadedFile
	 */
	public function getFile()
	{
		return $this->file;
	}

	public function upload()
	{
		// the file property can be empty if the field is not required
		if (null === $this->getFile()) {
			return;
		}

		$this->originalFileName = $this->getFile()->getClientOriginalName();

		do{
			$this->setPath(sprintf('%s/%s.%s', substr(md5(time().mt_rand()), 0, 3), substr(md5(time().mt_rand()), 0, 3), pathinfo($this->getFile()->getClientOriginalName(), PATHINFO_EXTENSION)));
		}while(file_exists($this->getAbsolutePath()));

		// use the original file name here but you should
		// sanitize it at least to avoid any security issues

		// move takes the target directory and then the
		// target filename to move to
		$this->getFile()->move(
			dirname($this->getAbsolutePath()),
			basename($this->getAbsolutePath())
		);

		// clean up the file property as you won't need it anymore
		$this->file = null;
	}
    /**
     * @var \DateTime
     */
    private $upload_date;


    /**
     * Set upload_date
     *
     * @param \DateTime $uploadDate
     * @return File
     */
    public function setUploadDate($uploadDate)
    {
        $this->upload_date = $uploadDate;

        return $this;
    }

    /**
     * Get upload_date
     *
     * @return \DateTime 
     */
    public function getUploadDate()
    {
        return $this->upload_date;
    }

	public function getSize(){
		return @filesize($this->getAbsolutePath());
	}

	public function getSizeFormatted(){
		return number_format($this->getSize() / 1024, 0, '', ' ').' KB';
	}

}
