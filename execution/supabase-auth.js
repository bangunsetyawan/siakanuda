import { initAuthCreds, BufferJSON } from '@whiskeysockets/baileys';

/**
 * Membantu mengembalikan data Buffer yang ter-serialize kembali ke objek Buffer Node.js asli.
 * Karena data JSONB dari Supabase mengubah Buffer menjadi objek biasa {type: 'Buffer', data: [...]}.
 */
function reviveBuffers(obj) {
    if (obj === null || obj === undefined) return obj;
    if (typeof obj === 'object') {
        if (obj.type === 'Buffer') {
            if (typeof obj.data === 'string') {
                return Buffer.from(obj.data, 'base64');
            }
            if (Array.isArray(obj.data)) {
                return Buffer.from(obj.data);
            }
        }
        for (const key in obj) {
            obj[key] = reviveBuffers(obj[key]);
        }
    }
    return obj;
}

/**
 * Membantu mempersiapkan objek yang memiliki Buffer agar aman di-serialize oleh Supabase JSONB.
 */
function replaceBuffers(obj) {
    return JSON.parse(JSON.stringify(obj, BufferJSON.replacer));
}

/**
 * Custom State Provider Baileys menggunakan Supabase Database
 * @param {import('@supabase/supabase-js').SupabaseClient} supabase
 */
async function useSupabaseAuthState(supabase) {
    let creds;
    
    try {
        console.log('[SUPABASE-AUTH] Mengambil credentials utama (creds)...');
        const { data, error } = await supabase
            .from('whatsapp_sessions')
            .select('data')
            .eq('id', 'creds')
            .maybeSingle();

        if (error) {
            console.error('[SUPABASE-AUTH] Error saat mengambil creds:', error);
        }

        if (data && data.data) {
            console.log('[SUPABASE-AUTH] Credentials ditemukan di database.');
            creds = reviveBuffers(data.data);
        } else {
            console.log('[SUPABASE-AUTH] Credentials tidak ditemukan. Membuat baru...');
            creds = initAuthCreds();
        }
    } catch (err) {
        console.error('[SUPABASE-AUTH] Gagal mengambil creds dari Supabase, menginisialisasi ulang:', err);
        creds = initAuthCreds();
    }

    // Fungsi untuk menyimpan kredensial utama
    const saveCreds = async () => {
        try {
            console.log('[SUPABASE-AUTH] Menyimpan credentials (creds)...');
            const { error } = await supabase
                .from('whatsapp_sessions')
                .upsert({
                    id: 'creds',
                    data: replaceBuffers(creds),
                    updated_at: new Date().toISOString()
                });
            if (error) {
                console.error('[SUPABASE-AUTH] Gagal menyimpan creds:', error);
                throw error;
            }
            console.log('[SUPABASE-AUTH] Credentials berhasil disimpan.');
        } catch (err) {
            console.error('[SUPABASE-AUTH] Gagal menyimpan creds ke Supabase:', err);
        }
    };

    return {
        state: {
            creds,
            keys: {
                get: async (type, ids) => {
                    console.log(`[SUPABASE-AUTH] keys.get: type=${type}, ids=`, ids);
                    const dataObj = {};
                    try {
                        await Promise.all(
                            ids.map(async (id) => {
                                const dbId = `${type}-${id}`;
                                const { data, error } = await supabase
                                    .from('whatsapp_sessions')
                                    .select('data')
                                    .eq('id', dbId)
                                    .maybeSingle();
                                
                                if (error) {
                                    console.error(`[SUPABASE-AUTH] Error keys.get untuk id=${dbId}:`, error);
                                }
                                if (data && data.data) {
                                    dataObj[id] = reviveBuffers(data.data);
                                }
                            })
                        );
                    } catch (err) {
                        console.error(`[SUPABASE-AUTH] Gagal mengambil keys untuk type ${type}:`, err);
                    }
                    return dataObj;
                },
                set: async (data) => {
                    console.log('[SUPABASE-AUTH] keys.set: data keys=', Object.keys(data));
                    const tasks = [];
                    try {
                        for (const type in data) {
                            for (const id in data[type]) {
                                const value = data[type][id];
                                const dbId = `${type}-${id}`;
                                if (value) {
                                    console.log(`[SUPABASE-AUTH] Meng-upsert key: ${dbId}`);
                                    tasks.push(
                                        supabase
                                            .from('whatsapp_sessions')
                                            .upsert({
                                                id: dbId,
                                                data: replaceBuffers(value),
                                                updated_at: new Date().toISOString()
                                            })
                                            .then(({ error }) => {
                                                if (error) console.error(`[SUPABASE-AUTH] Error upsert key ${dbId}:`, error);
                                            })
                                    );
                                } else {
                                    console.log(`[SUPABASE-AUTH] Menghapus key: ${dbId}`);
                                    tasks.push(
                                        supabase
                                            .from('whatsapp_sessions')
                                            .delete()
                                            .eq('id', dbId)
                                            .then(({ error }) => {
                                                if (error) console.error(`[SUPABASE-AUTH] Error delete key ${dbId}:`, error);
                                            })
                                    );
                                }
                            }
                        }
                        await Promise.all(tasks);
                        console.log('[SUPABASE-AUTH] Semua task keys.set selesai.');
                    } catch (err) {
                        console.error('[SUPABASE-AUTH] Gagal memperbarui keys di Supabase:', err);
                    }
                }
            }
        },
        saveCreds
    };
}

export { useSupabaseAuthState };
