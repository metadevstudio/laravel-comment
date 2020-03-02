## Usage

``` php
$user = App\User::first();
$product = App\Product::first();
// $user->comment(Commentable $model, $comment = '', $rate = 0);
$user->comment($product, 'Lorem ipsum ..', 3);
// approve it -- if the user model `canCommentWithoutApprove()` or you don't use `mustBeApproved()`, it is not necessary
$product->comments[0]->approve();
// get avg rating -- it calculates approved average rate.
$product->averageRate();
// get total comments count -- it calculates approved comments count.
$product->totalCommentsCount();
```