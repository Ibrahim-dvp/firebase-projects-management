import admin from "firebase-admin";
import { promises as fs } from "fs";
import { parse } from "csv-parse/sync";

const [csvPath, credentialsPath] = process.argv.slice(2);

async function importUsers() {
    try {
        // 1. Load Firebase credentials
        const serviceAccount = JSON.parse(await fs.readFile(credentialsPath));
        admin.initializeApp({
            credential: admin.credential.cert(serviceAccount),
        });

        // 2. Read CSV
        const csvData = await fs.readFile(csvPath, "utf8");
        const users = parse(csvData, { columns: true });

        // 3. Import users
        console.log("Starting import...");
        for (const user of users) {
            await admin.auth().createUser({
                email: user.email,
                password: user.password,
                emailVerified: false,
            });
            console.log(`Created: ${user.email}`);
        }
        console.log("Import complete!");
    } catch (error) {
        console.error("Error:", error.message);
        process.exit(1);
    }
}

importUsers();
