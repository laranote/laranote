import {Server} from '@hocuspocus/server'
import * as dotenv from 'dotenv'
import {Database} from "@hocuspocus/extension-database";
import mysql from 'mysql2/promise'
import axios from 'axios'

dotenv.config()

const pool = mysql.createPool({
    host: process.env.DB_HOST,
    port: process.env.DB_PORT,
    user: process.env.DB_USERNAME,
    password: process.env.DB_PASSWORD,
    database: process.env.DB_DATABASE,
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0
})

const server = Server.configure({
    port: process.env.HOCUSPOCUS_PORT || 1234,
    extensions: [
        new Database({
            fetch: async ({documentName}) => {
                try {
                    const decodedName = decodeURIComponent(documentName);
                    const parts = decodedName.split(':');

                    if (parts.length !== 2 || !parts[0].includes('Post')) {
                        console.error('Invalid document name format:', documentName);
                        return null;
                    }

                    const postId = parts[1];

                    const [rows] = await pool.query(
                        `
                            SELECT data
                            FROM posts
                            WHERE id = ?
                        `,
                        [postId]
                    );

                    return rows[0]?.data;
                } catch (error) {
                    console.error('Error fetching document:', error);
                    throw error;
                }
            },
            store: async ({documentName, state}) => {
                try {
                    const decodedName = decodeURIComponent(documentName);
                    const parts = decodedName.split(':');

                    if (parts.length !== 2 || !parts[0].includes('Post')) {
                        console.error('Invalid document name format:', documentName);
                        return;
                    }

                    const postId = parts[1];

                    await pool.query(
                        `
                            UPDATE posts
                            SET data = ?
                            WHERE id = ?
                        `,
                        [state, postId]
                    );
                } catch (error) {
                    console.error('Error storing document:', error);
                    throw error;
                }
            },
        }),
    ],
    async onAuthenticate(data) {
        const {token} = data;
        const params = new URLSearchParams(data.requestParameters);

        if (token === "public") {
            axios.post(process.env.APP_URL + '/api/posts/is-public', {
                post_id: params.get("post_id"),
            })
                .then(function (response) {
                    if (response.status !== 200) {
                        throw new Error('Not Public');
                    }

                    if (response.data.is_public === true) {
                        data.connection.readOnly = true;
                        return;
                    }

                    throw new Error('Not Public');
                })
                .catch(function (error) {
                    throw new Error('Unexpected error: ' + error);
                });
        } else {
            axios.post(process.env.APP_URL + '/api/user/can-edit', {
                token: token
            })
                .then(function (response) {

                    if (response.data.can_edit === false) {
                        data.connection.readOnly = true;
                    }

                    // Do nothing if can_edit is true (editor)
                })
                .catch(function (error) {
                    if (error.response && error.response.status === 404) {
                        throw new Error('Invalid token provided.');
                    } else {
                        throw new Error('Unexpected error: ' + error);
                    }
                });
        }
    }
})

server.listen()
