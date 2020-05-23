<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use Notifiable;


    const IS_ADMIN = 1;
    const IS_NORMAL = 0;
    const IS_BANNED = 1;
    const IS_ACTIVE = 0;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function post()
    {
        return $this->hasMany(Post::class);
    }

    //добавляю нового пользователя принимаю поля
    public static function add($fields)
    {
        //инстанцирую экземпляр класса User
        $user = new static;
        $user->fill($fields);
        //хешируется пароль из $fields достаю пароль
        $user->password = bcrypt($fields['password']);
        $user->save();

        //возвращаю сохранённого пользователя
        return $user;
    }

    //изминение пользователя
    public function edit($fields)
    {
        $this->fill($fields);
        $this->password = bcrypt($fields['password']);
        $this->save();
    }

    //удаление пользователя
    public function remove()
    {
        //если загружается новая картинка она проверяется если есть то старая удаляется, если нет то записывается новая
        Storage::delete('uploads/' . $this->image);
        $this->delete();
    }

    //загрузка и обновление картинки поста
    public function uploadAvatar($image)
    {
        if ($image == null) { return; }
        //если загружается новая картинка она проверяется если есть то старая удаляется, если нет то записывается новая
        Storage::delete('uploads/' . $this->image);

        //задаю переменной $filename рандомное имя с длинной 10 символов и указываю расширение
        $filename = Str::random(10) . '.' . $image->extension();

        //сохраняю картинку в папку uploads
        $image->saveAs('uploads/', $filename);

        //записываю полученное значение в поле image и сохраняю в БД
        $this->image = $filename;
        $this->save();
    }

    //метод для вывода автарки
    public function getImage()
    {
        //если нет картинки то выводим заглушку
        if ($this->image == null)
        {
            return '/img/no-user.png';
        }
        return '/uploads/' . $this->image;
    }

    //сделать админом
    public function makeAdmin()
    {
        $this->is_admin = User::IS_ADMIN;
        $this->save();
    }

    //убрать ранг админа
    public function makeNormal()
    {
        $this->is_admin = User::IS_NORMAL;
        $this->save();
    }

    public function toggleAdmin($value)
    {
        if ($value == null)
        {
            return $this->makeNormal();
        }
        return $this->makeAdmin();
    }

    public function ban()
    {
        $this->status = User::IS_BANNED;
        $this->save();
    }

    //убрать ранг админа
    public function unban()
    {
        $this->status = User::IS_ACTIVE;
        $this->save();
    }

    public function toggleBan($value)
    {
        if ($value == null)
        {
            return $this->unban();
        }
        return $this->ban();
    }

}
