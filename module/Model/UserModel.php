<?php
// {{{ Header
/**
 *
 * @author joerg.mueller
 * @version $Id:$
 */
// }}}

namespace Model;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\Common\Collections\Criteria;

use Model\UserSheetModel;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;


/**
 * @MongoDB\Document(collection="Users", indexes={
 *	 @MongoDB\Index(keys={"sheet.coordinates"="2d"})
 * },
 * repositoryClass="Repositories\UserModelRepository")
 * @MongoDB\HasLifecycleCallbacks
 */
class UserModel implements InputFilterAwareInterface
{

	protected $inputFilter;


	/**
	 * @MongoDB\Id
	 */
	protected $id;

	/**
	 * @MongoDB\Distance
	 */
	public $distance;

	/**
	 * @MongoDB\Field(type="date")
	 */
	protected $datecreate;

	/**
	 * @MongoDB\Field(type="date")
	 */
	protected $lastlogin;

	/**
	 * @MongoDB\Field(type="string")
	 * @MongoDB\Index(unique=true, sparse=true, dropDups=true)
	 */
	protected $nickname;

	/**
	 * @MongoDB\Field(type="string")
	 * @MongoDB\Index(unique=true, sparse=true, dropDups=true)
	 */
	protected $email;

	/**
	 * @MongoDB\Field(type="string")
	 * @MongoDB\Index(sparse=true)
	 */
	protected $password;

	/**
	 * @MongoDB\Field(type="int")
	 * @MongoDB\Index(sparse=true)
	 */
	protected $visible=0;

	/** @MongoDB\EmbedOne(targetDocument="UserSheetModel") */
	protected $sheet;

	/**
	 * @MongoDB\Field(type="string")
	 * @MongoDB\Index(sparse=true)
	 */
	protected $role='user';

	/**
	 * @MongoDB\Field(type="string")
	 * @MongoDB\Index(unique=true, sparse=true, dropDups=true)
	 */
	protected $token;

	/**
	 * @MongoDB\Field(type="collection")
	 * @MongoDB\Index(order="asc")
	 */
	protected $groups = array();

	/** @MongoDB\ReferenceOne(targetDocument="ClientModel") */
	protected $client;

	/** @MongoDB\ReferenceOne(targetDocument="UserModel") */
	protected $parent;

	/** @MongoDB\ReferenceMany(targetDocument="UserModel") */
	protected $friends;

	/** @MongoDB\ReferenceMany(targetDocument="UserModel") */
	protected $likes;

	/** @MongoDB\ReferenceMany(targetDocument="UserModel") */
	protected $dislikes;

	/** @MongoDB\ReferenceMany(targetDocument="UserModel") */
	protected $banned;

	public function __construct( Array $params=array() )
	{
		$this->datecreate = new \DateTime();
		$this->token = self::newPassword(16, null, 0);
		foreach($params as $k => $v) if(isset($this->$k)) $this->$k = $v;
	}

	public function toArray()
	{
		$user = get_object_vars($this);
		$user['sheet'] = isset($user['sheet']) && is_object($user['sheet']) ? $this->getSheet()->toArray() : $user['sheet'];
		$user['client'] = isset($user['client']) && is_object($user['client']) ? $this->getClient()->toArray() : $user['client'];
		$user['parent'] = isset($user['parent']) && is_object($user['parnet']) ? $this->getParent()->toArray() : $user['parent'];
		return $user;
	}

	/**
	 *
	 * @param number $maxLen
	 * @param string $pass
	 * @param number $flag
	 * @return Ambigous <string, unknown>
	 */
	public static function newPassword($maxLen = 8, $pass = '', $flag = 1)
	{
		srand( ( float ) microtime() * 10000000 );
		$array_a = range( 0, 9 );
		$array_b = range( 'a', 'z' );
		$array_c = range( 'A', 'Z' );
		$array_d = array(
			'$','_','+','*','!','-'
		);
		if ($flag === 0)
		{
			$array_d = array();
		}
		$params = array_merge( $array_a, $array_b, $array_c, $array_d );
		shuffle( $params );
		while ( strlen( $pass ) < $maxLen )
		{
			$char = $params[rand( 0, count( $params ) - 1 )];
			if (! strpos( $pass, $char ))
			{
				$pass .= $char;
			}
		}
		unset( $params );
		return $pass;
	}

	public function getFullName()
	{
		$sheet = $this->getSheet();
		return $sheet->getFirstname() . ' ' . $sheet->getName();
	}

	public function exchangeArray($data, $dm, $flag='user')
	{
		$this->nickname = (isset($data['nickname'])) ? $data['nickname'] : null;
		$this->email 	= (isset($data['email'])) ? $data['email'] : null;
		$this->password = (isset($data['password']) && strlen(trim($data['password'])))
							? md5($data['password'])
							: $this->password;

		if(isset($data['client']) && !empty($data['client'])) {
			if(is_string($data['client'])) {
				if($client = $dm->getRepository('Model\ClientModel')->find($data['client'])) {
					$this->setClient($client);
				}
			}

		}
		else $this->setClient(null);
	}

	public function setInputFilter(InputFilterInterface $inputFilter)
	{
		throw new \Exception("Not used");
	}

	public function getInputFilter()
	{
		if (!$this->inputFilter) {
			$inputFilter = new InputFilter();

			$inputFilter->add(array(
				'name'	 => 'nickname',
				'required' => true,
				'filters'  => array(
					array('name' => 'StripTags'),
					array('name' => 'StringTrim'),
				),
				'validators' => array(
					array(
						'name'	=> 'StringLength',
						'options' => array(
							'encoding' => 'UTF-8',
							'min'	  => 5,
							'max'	  => 101,
						),
					),
				),
			));

			$inputFilter->add(array(
				'name'	 => 'email',
				'required' => true,
				'filters'  => array(
					array('name' => 'StripTags'),
					array('name' => 'StringTrim'),
				),
				'validators' => array(
					array(
						'name'	=> 'StringLength',
						'options' => array(
							'encoding' => 'UTF-8',
							'min'	  => 5,
							'max'	  => 100,
						),
					),
					array(
						'name' => 'EmailAddress',
						'options' => array(
							'encoding' => 'UTF-8',
							'min'	=> 5,
							'max'	=> 255
						)
					)
				),
			));

			$this->inputFilter = $inputFilter;
		}

		return $this->inputFilter;
	}

	/**
	 * Get id
	 *
	 * @return id $id
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Set datecreate
	 *
	 * @param date $datecreate
	 * @return self
	 */
	public function setDatecreate($datecreate)
	{
		$this->datecreate = $datecreate;
		return $this;
	}

	/**
	 * Get datecreate
	 *
	 * @return date $datecreate
	 */
	public function getDatecreate()
	{
		return $this->datecreate;
	}

	/**
	 * Set lastlogin
	 *
	 * @param date $lastlogin
	 * @return self
	 */
	public function setLastlogin($lastlogin)
	{
		$this->lastlogin = $lastlogin;
		return $this;
	}

	/**
	 * Get lastlogin
	 *
	 * @return date $lastlogin
	 */
	public function getLastlogin()
	{
		return $this->lastlogin;
	}

	/**
	 * Set nickname
	 *
	 * @param string $nickname
	 * @return self
	 */
	public function setNickname($nickname)
	{
		$this->nickname = $nickname;
		return $this;
	}

	/**
	 * Get nickname
	 *
	 * @return string $nickname
	 */
	public function getNickname()
	{
		return $this->nickname;
	}

	/**
	 * Set email
	 *
	 * @param string $email
	 * @return self
	 */
	public function setEmail($email)
	{
		$this->email = $email;
		return $this;
	}

	/**
	 * Get email
	 *
	 * @return string $email
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * Set password
	 *
	 * @param string $password
	 * @return self
	 */
	public function setPassword($password)
	{
		$this->password = $password;
		return $this;
	}

	/**
	 * Get password
	 *
	 * @return string $password
	 */
	public function getPassword()
	{
		return $this->password;
	}

	/**
	 * Set visible
	 *
	 * @param int $visible
	 * @return self
	 */
	public function setVisible($visible)
	{
		$this->visible = $visible;
		return $this;
	}

	/**
	 * Get visible
	 *
	 * @return int $visible
	 */
	public function getVisible()
	{
		return $this->visible;
	}

	/**
	 * Set sheet
	 *
	 * @param Model\UserSheetModel $sheet
	 * @return self
	 */
	public function setSheet(\Model\UserSheetModel $sheet)
	{
		$this->sheet = $sheet;
		return $this;
	}

	/**
	 * Get sheet
	 *
	 * @return Model\UserSheetModel $sheet
	 */
	public function getSheet()
	{
		return $this->sheet;
	}

	/**
	 * Set role
	 *
	 * @param string $role
	 * @return self
	 */
	public function setRole($role)
	{
		$this->role = $role;
		return $this;
	}

	/**
	 * Get role
	 *
	 * @return string $role
	 */
	public function getRole()
	{
		return $this->role;
	}

	/**
	 * Set token
	 *
	 * @param string $token
	 * @return self
	 */
	public function setToken($token)
	{
		$this->token = $token;
		return $this;
	}

	/**
	 * Get token
	 *
	 * @return string $token
	 */
	public function getToken()
	{
		if(!$this->token) {
			$this->token = self::newPassword(16, null, 0);
		}
		return $this->token;
	}

	/**
	 * Add friend
	 *
	 * @param Model\UserModel $friend
	 */
	public function addFriend(\Model\UserModel $friend)
	{
		if($this->getBanned()->contains($friend)) $this->removeBanned($friend);
		if($this->getFriends()->contains($friend)) return $this;
		$this->friends[] = $friend;
		return $this;
	}

	/**
	 * Remove friend
	 *
	 * @param Model\UserModel $friend
	 */
	public function removeFriend(\Model\UserModel $friend)
	{
		$this->friends->removeElement($friend);
	}

	/**
	 * Get friends
	 *
	 * @return Doctrine\Common\Collections\ArrayCollection $friends
	 */
	public function getFriends()
	{
		return $this->friends;
	}

	/**
	 * Add like
	 *
	 * @param Model\UserModel $like
	 */
	public function addLike(\Model\UserModel $like)
	{
		if($this->getDislikes()->contains($like)) $this->removeDislike($like);
		if($this->getLikes()->contains($like)) return $this;
		$this->likes[] = $like;
		return $this;
	}

	/**
	 * Remove like
	 *
	 * @param Model\UserModel $like
	 */
	public function removeLike(\Model\UserModel $like)
	{
		$this->likes->removeElement($like);
	}

	/**
	 * Get likes
	 *
	 * @return Doctrine\Common\Collections\ArrayCollection $likes
	 */
	public function getLikes()
	{
		return $this->likes;
	}

	/**
	 * Add dislike
	 *
	 * @param Model\UserModel $dislike
	 */
	public function addDislike(\Model\UserModel $dislike)
	{
		if($this->getLikes()->contains($dislike)) $this->removeLike($dislike);
		if($this->getDislikes()->contains($dislike)) return $this;
		$this->dislikes[] = $dislike;
		return $this;
	}

	/**
	 * Remove dislike
	 *
	 * @param Model\UserModel $dislike
	 */
	public function removeDislike(\Model\UserModel $dislike)
	{
		$this->dislikes->removeElement($dislike);
	}

	/**
	 * Get dislikes
	 *
	 * @return Doctrine\Common\Collections\ArrayCollection $dislikes
	 */
	public function getDislikes()
	{
		return $this->dislikes;
	}

	/**
	 * Add banned
	 *
	 * @param Model\UserModel $banned
	 */
	public function addBanned(\Model\UserModel $banned)
	{
		if($this->getFriends()->contains($banned)) $this->removeFriend($banned);
		if($this->getBanned()->contains($banned)) return $this;
		$this->banned[] = $banned;
		return $this;
	}

	/**
	 * Remove banned
	 *
	 * @param Model\UserModel $banned
	 */
	public function removeBanned(\Model\UserModel $banned)
	{
		$this->banned->removeElement($banned);
	}

	/**
	 * Get banned
	 *
	 * @return Doctrine\Common\Collections\ArrayCollection $banned
	 */
	public function getBanned()
	{
		return $this->banned;
	}

	/**
	 * Set client
	 *
	 * @param Model\ClientModel $client
	 * @return self
	 */
	public function setClient( \Model\ClientModel $client=null )
	{
		$this->client = $client;
		return $this;
	}

	/**
	 * Get client
	 *
	 * @return Model\ClientModel $client
	 */
	public function getClient()
	{
		return $this->client;
	}


    /**
     * Set parent
     *
     * @param Model\UserModel $parent
     * @return self
     */
    public function setParent(\Model\UserModel $parent)
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * Get parent
     *
     * @return Model\UserModel $parent
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set groups
     *
     * @param collection $groups
     * @return self
     */
    public function setGroups($groups)
    {
        $this->groups = $groups;
        return $this;
    }

    /**
     * Get groups
     *
     * @return collection $groups
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Set distance
     *
     * @param string $distance
     * @return self
     */
    public function setDistance($distance)
    {
        $this->distance = $distance;
        return $this;
    }

    /**
     * Get distance
     *
     * @return string $distance
     */
    public function getDistance()
    {
        return $this->distance;
    }
}
