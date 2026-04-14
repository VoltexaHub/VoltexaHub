import './echo';

const container = document.querySelector('[data-thread-posts]');
if (container) {
    const threadId = container.dataset.threadId;

    window.Echo.channel(`threads.${threadId}`)
        .listen('.post.created', (payload) => {
            if (document.getElementById(`post-${payload.id}`)) return;

            const article = document.createElement('article');
            article.className = 'bg-white rounded-lg shadow-sm border border-indigo-300 overflow-hidden ring-1 ring-indigo-100';
            article.id = `post-${payload.id}`;

            const authorHtml = payload.author
                ? `<a href="${payload.author.profile_url}" class="flex items-center gap-2 hover:opacity-80">
                       <img src="${payload.author.avatar_url}" alt="" class="w-7 h-7 rounded-full" />
                       <span class="font-medium text-gray-800 hover:text-indigo-600">${escapeHtml(payload.author.name)}</span>
                   </a>`
                : '<span class="text-gray-500">[deleted]</span>';

            article.innerHTML = `
                <header class="px-4 py-2 bg-indigo-50 border-b border-indigo-200 flex items-center justify-between text-sm">
                    <div class="flex items-center gap-2">${authorHtml}</div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-semibold text-indigo-600 uppercase">New</span>
                        <span class="text-gray-500">${escapeHtml(payload.created_at_formatted)}</span>
                    </div>
                </header>
                <div class="px-4 py-4 prose prose-sm max-w-none prose-indigo">${payload.body_html}</div>
            `;

            container.appendChild(article);
        });
}

function escapeHtml(str) {
    return String(str).replace(/[&<>"']/g, (c) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
    }[c]));
}
