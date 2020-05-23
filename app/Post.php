<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Post extends Model
{
    //поля которые я буду сохранять в БД. Вне зависимости сколько полей получит метод add.
    protected $fillable = ['title', 'content', 'user_id'];

    public function category()
    {
        //возвращает одну категорию из БД используя модель Category
        return $this->hasOne(Category::class);
    }

    public function author()
    {
        //возвращает автора статьи из БД используя модель User
        return $this->hasOne(User::class);
    }

    //принимает поля для массового сохранения данных поста
    public static function add($fields)
    {
        $post = new static;
        $post->fill($fields);
        $post->save();
        //для того что бы посторонний не мог подменить id, прописываю id программно
        $post->user_id = 1;
        //возвращаю сохранённый пост
        return $post;
    }

    //изминение поста, принимает и сохранаяет поля в таблицу
    public function edit($fields)
    {
        $this->fill($fields);
        $this->save();
    }

    //удаление поста.
    public function remove()
    {
        //при удалении статьи удалится картинка поста
        Storage::delete('uploads/' . $this->image);
        //удаляется сам пост
        $this->delete();
    }

    //загрузка и обновление картинки поста
    public function uploadImage($image)
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

    //добавляю категорию посту по id
    public function setCategory($id)
    {
        if ($id == null) { return; }

        $this->category_id = $id;
        $this->save();
    }

    //метод для скрытия поста (черновик)
    public function setDraft()
    {
        $this->status = 0;
        $this->save();
    }

    //метод для опубликования поста
    public function setPublic()
    {
        $this->status = 1;
        $this->save();
    }

    //переключатель скрытия или опубликования статьи получает значения чек бокса, выбран или нет
    public function toggleStatus($value)
    {
        //если получает null то попадает в черновик
        if ($value == null)
        {
           return $this->setDraft();
        }
       return $this->setPublic();

    }

    //метод для вывода картинки
    public function getImage()
    {
        //если нет картинки то выводим заглушку
        if ($this->image == null)
        {
            return '/img/no-image.png';
        }
        return '/uploads/' . $this->image;
    }

}
