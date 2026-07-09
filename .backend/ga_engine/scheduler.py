import sqlite3
import pandas as pd
import random
from datetime import datetime, timedelta

from chromosome import Gene, Chromosome

class Scheduler:
    def __init__(self, db_file="exam_tabling.db", start_date=None, end_date=None):
        self.db_file = db_file
        self.exam_start = start_date  
        self.exam_end = end_date      
        self.sections = {}          
        self.section_details = {}   
        self.rooms = {}             
        self.timeslots = []         
        self.timeslot_details = {}  
        self.enrollments = {}       
        self._load_data()

    def _load_data(self):
        conn = None
        try:
            conn = sqlite3.connect(self.db_file)
            cursor = conn.cursor()

            cursor.execute("SELECT ma_lhp, ten_lhp, stc, so_sv FROM lophocphan;")
            for ma_lhp, ten_lhp, stc, so_sv in cursor.fetchall():
                so_sv_thuc_te = so_sv if so_sv is not None else 0
                self.sections[ma_lhp] = so_sv_thuc_te
                self.section_details[ma_lhp] = {
                    'course_name': ten_lhp,
                    'stc': stc if stc is not None else 0,
                    'num_students': so_sv_thuc_te
                }

            cursor.execute("SELECT ma_phong, suc_chua FROM phongthi;")
            for ma_phong, suc_chua in cursor.fetchall():
                self.rooms[ma_phong] = suc_chua

            cursor.execute("SELECT mssv, ma_lhp FROM dslophocphan;")
            for mssv, ma_lhp in cursor.fetchall():
                if mssv not in self.enrollments:
                    self.enrollments[mssv] = []
                self.enrollments[mssv].append(ma_lhp)

            if self.exam_start and self.exam_end:
                delta = self.exam_end - self.exam_start
                total_days = delta.days + 1
                
                for day in range(total_days):
                    current_date = self.exam_start + timedelta(days=day)
                    
                    if current_date.weekday() == 6: # Bỏ qua Chủ Nhật
                        continue
                        
                    ngay_thi = current_date.strftime('%Y-%m-%d')
                    for ca_thi in range(1, 5): 
                        ts_id = f"{ngay_thi}_Ca{ca_thi}"
                        self.timeslots.append(ts_id)
                        self.timeslot_details[ts_id] = {
                            'ngay_thi': ngay_thi,
                            'ca_thi': ca_thi
                        }

        except sqlite3.Error as e:
            print(f"Lỗi DB: {e}")
        finally:
            if conn:
                conn.close()

def generate_initial_chromosome(scheduler):
    chromosome = Chromosome()
    building_a_course_names = ['An toàn bảo mật thông tin trong kinh doanh', 'Phân tích dữ liệu cho tài chính', 'Trực quan hóa dữ liệu']
    building_a_prefixes = ['CNL', 'ELI']

    rooms_A_pool = {ma_phong: cap for ma_phong, cap in scheduler.rooms.items() if ma_phong.startswith('A')}
    rooms_C_pool = {ma_phong: cap for ma_phong, cap in scheduler.rooms.items() if ma_phong.startswith('C')}

    for ma_lhp, num_students in scheduler.sections.items():
        course_name = scheduler.section_details.get(ma_lhp, {}).get('course_name', '')
        
        if course_name in building_a_course_names or any(ma_lhp.startswith(prefix) for prefix in building_a_prefixes):
            target_building = 'A'
        else:
            target_building = 'C'

        eligible_rooms = rooms_A_pool if target_building == 'A' else rooms_C_pool
        if not eligible_rooms:
            eligible_rooms = scheduler.rooms 
            
        if not scheduler.timeslots:
            continue
            
        timeslot_id = random.choice(scheduler.timeslots)
        assigned_rooms = []
        current_assigned_capacity = 0
        
        shuffled_room_ids = list(eligible_rooms.keys())
        random.shuffle(shuffled_room_ids)

        for room_id in shuffled_room_ids:
            if current_assigned_capacity >= num_students:
                break 
            assigned_rooms.append(room_id)
            current_assigned_capacity += eligible_rooms[room_id]

        if not assigned_rooms and num_students > 0:
            assigned_rooms = [random.choice(shuffled_room_ids)] if shuffled_room_ids else ['NO_ROOM_ASSIGNED']

        chromosome.add_gene(Gene(ma_lhp, timeslot_id, assigned_rooms))
    return chromosome

def run_genetic_algorithm(scheduler, pop_size=40, max_gen=40, crossover_rate=0.8, mutation_rate=0.1):
    if not scheduler.sections or not scheduler.timeslots:
        return None

    population = []
    fitness_calc = FitnessCalculator(scheduler)

    for _ in range(pop_size):
        chrom = generate_initial_chromosome(scheduler)
        fitness_calc.calculate_fitness(chrom)
        population.append(chrom)

    if not population: return None

    best_overall = max(population, key=lambda c: c.fitness).clone()

    for gen in range(max_gen):
        new_population = [best_overall.clone()]
        while len(new_population) < pop_size:
            parent1 = tournament_selection(population)
            parent2 = tournament_selection(population)

            if random.random() < crossover_rate:
                child1, child2 = uniform_crossover(parent1, parent2)
            else:
                child1, child2 = parent1.clone(), parent2.clone()

            mutate(child1, scheduler, mutation_rate)
            mutate(child2, scheduler, mutation_rate)
            repair_chromosome(child1, scheduler)
            repair_chromosome(child2, scheduler)

            new_population.extend([child1, child2])

        population = new_population[:pop_size]

        for chrom in population:
            fitness_calc.calculate_fitness(chrom)

        current_best = max(population, key=lambda c: c.fitness)
        if current_best.fitness > best_overall.fitness:
            best_overall = current_best.clone()

        if best_overall.fitness == 1.0:
            break

    return best_overall

def save_to_db(best_schedule, scheduler, db_file="exam_tabling.db"):
    """Lưu kết quả trực tiếp vào SQLite để Web PHP lấy lên"""
    conn = sqlite3.connect(db_file)
    cursor = conn.cursor()
    cursor.execute("DELETE FROM lichthi;") 
    
    for gene in best_schedule.genes:
        ma_lhp = gene.section_id
        ts_info = scheduler.timeslot_details.get(gene.timeslot_id, {})
        ngay_thi = ts_info.get('ngay_thi', '1970-01-01')
        ca_thi = ts_info.get('ca_thi', 1)
        so_sv = scheduler.sections.get(ma_lhp, 0)
        phong_thi_str = ', '.join(gene.room_ids)
        
        cursor.execute("""
            INSERT INTO lichthi (ma_lhp, ngay_thi, ca_thi, ma_phong, so_sv_phong)
            VALUES (?, ?, ?, ?, ?)
        """, (ma_lhp, ngay_thi, ca_thi, phong_thi_str, so_sv))
        
    conn.commit()
    conn.close()
