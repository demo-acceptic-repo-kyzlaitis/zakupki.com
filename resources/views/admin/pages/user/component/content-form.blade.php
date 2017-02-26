
<div class="form-group">
    <label for="title" class="col-sm-4 control-label">Ім'я</label>
    <div class="col-lg-4">
        {!! Form::text('name',null,
        ['class' => 'form-control', 'placeholder' =>'', 'required'  ]) !!}
    </div>
</div>

<div class="form-group">
    <label for="title" class="col-sm-4 control-label">Email</label>
    <div class="col-lg-4">
        {!! Form::email('email',null,
           ['id'=>'inputEmail', 'class' => 'form-control', 'placeholder' =>'',
            'required'
           ]) !!}
    </div>
</div>

    <div class="form-group">
        <label for="title" class="col-sm-4 control-label">Пароль</label>
        <div class="col-lg-4">
            {!! Form::password('password',
                ['id'=>'inputPassword', 'class' => 'form-control', 'placeholder' =>'',

                ]) !!}
        </div>
    </div>



    <div class="form-group">
        <label for="title" class="col-sm-4 control-label">Підтвердження паролю</label>
        <div class="col-lg-4">
            {!! Form::password('password_confirmation',
                ['id'=>'inputPasswordConfirmation', 'class' => 'form-control', 'placeholder' =>'',

                ]) !!}
        </div>
    </div>

    <div class="form-group">
        <label for="roles" class="col-sm-4 control-label">Роли пользователя</label>
        <div class="col-lg-4">
            @foreach($roles as $role)
                <label>{!! Form::checkbox('roles[' . $role->id . ']',1, (in_array($role->id, $userRoles)) ? true : null) !!} {{$role->name}}</label> <br>
            @endforeach
        </div>
    </div>
