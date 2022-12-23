drop table if exists resolutions_dates;
drop table if exists habit;
drop table if exists resolution;

create table habit (
    id int not null auto_increment primary key,
    name varchar(255),
    description varchar(1024),
    start_date date not null,
    end_date date null default null,
    created_time datetime not null,
    changed_time datetime not null,
    unique index (name)
) DEFAULT CHARSET=utf8;

create table resolution (
    id int not null auto_increment primary key,
    habit_id int not null,
    name varchar(255) not null,
    abbreviation char(1) not null,
    description varchar(1024) null default null,
    fulfilment_percent integer not null default 100,
    created_time datetime not null,
    changed_time datetime not null,
    unique index(habit_id, name),
    unique index(abbreviation),
    constraint fk_resolution_habit_id foreign key (habit_id) references habit(id) on delete cascade on update cascade
) DEFAULT CHARSET=utf8;

create table resolutions_dates (
    id int not null auto_increment primary key,
    resolution_id int not null,
    `date` date not null,
    comment varchar(1024) null default null,
    created_time datetime not null,
    changed_time datetime not null,
    unique index (resolution_id, `date`),
    constraint fk_habits_resolutions_resolution_id foreign key (resolution_id) references resolution(id) on delete cascade on update cascade
) DEFAULT CHARSET=utf8;
