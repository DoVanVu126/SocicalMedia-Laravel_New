<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    protected $fillable = ['post_id', 'user_id', 'content'];

   private $badWords = [
    // Tiếng Việt
    'chửi', 'đánh', 'giết', 'bạo lực', 'đụ', 'cặc', 'lồn', 'đĩ', 'điếm',
    'đồ chó', 'ngu', 'đần', 'khùng', 'thằng điên', 'con điên', 'bố láo',
    'bố đời', 'mẹ mày', 'địt', 'vãi', 'vãi lồn', 'thằng chó', 'con chó',
    'mất dạy', 'láo toét', 'láo', 'khốn', 'khốn nạn', 'vô học', 'mất nết',
    'vô đạo đức', 'xàm lồn', 'rảnh lồn', 'rảnh háng', 'bóc phốt', 'tởm',
    'thằng ngu', 'con ngu', 'thằng đần', 'con đần', 'bệnh hoạn', 'thằng khùng',
    'ăn hại', 'xấc láo', 'trơ trẽn', 'mất dạy', 'súc vật', 'đầu bò', 'chó chết',
    'chó má', 'bê đê', 'ái nam ái nữ', 'bóng lộ', 'đồ rác', 'đồ bỏ đi',
    'thứ vô dụng', 'mặt lồn', 'đầu buồi', 'cái lồn', 'bướm', 'mả mẹ', 'nói láo',
    'nói phét', 'nổ banh xác', 'đâm sau lưng', 'đâm chọt', 'bóc lột', 'hại não',
    'lật mặt', 'bắt nạt', 'gian dối', 'lừa đảo', 'phản động', 'khủng bố',
    'bán nước', 'hèn hạ', 'nhục nhã', 'vô liêm sỉ', 'thô tục', 'tục tĩu',

    // English
    'fuck', 'shit', 'asshole', 'bitch', 'damn', 'son of a bitch', 'bastard',
    'dick', 'pussy', 'slut', 'retard', 'moron', 'whore', 'cunt', 'jerk',
    'scumbag', 'loser', 'freak', 'twat', 'cock', 'motherfucker', 'bullshit',
    'fucker', 'shithead', 'douchebag', 'crap', 'suck', 'dumb', 'stupid',
    'ugly', 'idiot', 'fatass', 'jackass', 'pig', 'skank', 'hoe', 'trashy',
    'bloody', 'wanker', 'prick', 'arse', 'arsehole', 'knob', 'slag', 'nuts',
    'retarded', 'spastic', 'dipshit', 'nutsack', 'tit', 'knobhead', 'bellend',
    'minge', 'bugger', 'piss', 'twit', 'git', 'jerkwad', 'craphole', 'shitface',
    'bitchy', 'numbnuts', 'twatwaffle', 'shitbag', 'butthead', 'dickhead',
    'ballbag', 'fuckwit', 'dumbass', 'nutjob', 'nutcase', 'prickface', 'shitlord',
    'shitstain', 'slutty', 'asswipe', 'cockhead', 'cockface', 'fuckface', 'dipshit',
    'motherless', 'bitchass', 'arsewipe', 'ballsack', 'assclown', 'shitbrains',
    'asshat', 'scrote', 'shitlicker', 'ballsucker', 'fucktard', 'fuckboy',
    'cocksucker', 'twatface', 'knobjockey', 'whoremonger', 'shitweasel',
    'shitstorm', 'fartknocker', 'assmaster', 'dickweasel', 'cumdumpster',
    'douchecanoe', 'fucknugget', 'shitgibbon', 'jerkoff', 'balllicker', 'crapweasel'
];

    private function filterBadWords($text)
    {
        foreach ($this->badWords as $word) {
            $pattern = '/\b' . preg_quote($word, '/') . '\b/iu';
            $replacement = str_repeat('*', mb_strlen($word));
            $text = preg_replace($pattern, $replacement, $text);
        }
        return $text;
    }

    public function index($postId)
    {
        $comments = Comment::where('post_id', $postId)
            ->with('user:id,username')
            ->latest()
            ->get();

        return response()->json($comments);
    }

    public function store(Request $request, $postId)
    {
        $request->validate([
            'content' => 'required|string',
            'user_id' => 'required|exists:users,id',
        ]);

        $filteredContent = $this->filterBadWords($request->content);

        $comment = Comment::create([
            'post_id' => $postId,
            'user_id' => $request->user_id,
            'content' => $filteredContent,
        ]);

        return response()->json($comment, 201);
    }
    public function update(Request $request, $postId, $commentId)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
            'user_id' => 'required|exists:users,id',
        ]);
        $comment = Comment::where('id', $commentId)
            ->where('post_id', $postId)
            ->first();
        if (!$comment) {
            return response()->json(['message' => 'Bình luận không tìm thấy.'], 404);
        }
        if ($comment->user_id != $request->user_id) {
            return response()->json(['message' => 'Bạn không có quyền sửa bình luận này.'], 403);
        }

        $filteredContent = $this->filterBadWords($request->content);
        $comment->content = $filteredContent;
        $comment->save();
        return response()->json($comment->load('user:id,username'), 200);
    }
    public function destroy(Request $request, $postId, $commentId)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);
        $comment = Comment::where('id', $commentId)
            ->where('post_id', $postId)
            ->first();
        if (!$comment) {
            return response()->json(['message' => 'Bình luận không tìm thấy.'], 404);
        }
        if ($comment->user_id != $request->user_id) {
            return response()->json(['message' => 'Bạn không có quyền xóa bình luận này.'], 403);
        }
        $comment->delete();
        return response()->json(['message' => 'Bình luận đã được xóa thành công.'], 200);
    }
}
